<?php

namespace App\Livewire\Student;

use App\Models\ActivityLog;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExamTaking extends Component
{
    public ExamSession $session;

    public Exam $exam;

    public Collection $questions;

    public array $answers = [];

    public int $remainingSeconds = 0;

    public bool $isSubmitting = false;

    public bool $showSubmitModal = false;

    // Anti-cheat guards
    public int $tabSwitchCount = 0;

    public bool $showTabWarning = false;

    public string $tabSwitchWarning = '';

    public bool $isIpChanged = false;

    public string $ipChangeWarning = '';

    public int $maxTabSwitches = 3;

    public function mount(ExamSession $session): void
    {
        Log::info('[ExamTaking] mount() called', [
            'session_id' => $session->id,
            'exam_id'    => $session->exam_id,
            'user_id'    => $session->user_id,
        ]);

        $this->session = $session;
        $this->exam    = $session->exam;

        $this->tabSwitchCount = $session->tab_switch_count ?? 0;
        $this->maxTabSwitches = $this->exam->max_tab_switches ?? 3;

        $this->checkIpChange();

        if ($session->is_submitted) {
            $this->redirectRoute('student.results', ['session' => $session]);
            return;
        }

        // Stable question order: persist IDs on first load, reload in same order thereafter
        if (! empty($session->question_ids)) {
            $ordered = collect($session->question_ids);
            $this->questions = Question::whereIn('id', $ordered)
                ->with('options')
                ->get()
                ->sortBy(fn ($q) => $ordered->search($q->id))
                ->values();
        } else {
            $this->questions = $this->exam->selectQuestionsForStudent();
            $this->session->update(['question_ids' => $this->questions->pluck('id')->all()]);
        }

        $this->session->update(['total_received' => $this->questions->sum('marks')]);

        $this->remainingSeconds = $this->getRemainingSeconds();

        if ($this->remainingSeconds <= 0) {
            $this->submit();
            return;
        }

        // Load existing answers.
        // IMPORTANT: use string keys ((string) $question_id) throughout so keys
        // survive Livewire's JSON round-trip without type mismatch.
        $session->load('answers');
        foreach ($session->answers as $answer) {
            $this->answers[(string) $answer->question_id] = [
                'selected_option_id' => $answer->selected_option_id,
                'selected_options'   => array_values($answer->selected_options ?? []),
                'text_answer'        => $answer->text_answer,
            ];
        }
    }

    protected function checkIpChange(): void
    {
        $currentIp  = request()->ip();
        $originalIp = $this->session->ip_address;

        if ($originalIp !== $currentIp) {
            ActivityLog::create([
                'session_id' => $this->session->id,
                'event_type' => 'ip_change',
                'metadata'   => [
                    'original_ip' => $originalIp,
                    'new_ip'      => $currentIp,
                    'changed_at'  => now()->toIso8601String(),
                ],
                'occurred_at' => now(),
            ]);

            $this->session->update([
                'is_flagged'  => true,
                'flag_reason' => 'IP address changed: '.$originalIp.' -> '.$currentIp,
            ]);

            $this->isIpChanged     = true;
            $this->ipChangeWarning = 'Your session has been flagged due to network changes during the exam.';
        }
    }

    public function handleTabSwitch(): void
    {
        if ($this->session->is_submitted) {
            return;
        }

        $this->tabSwitchCount++;

        ActivityLog::create([
            'session_id'  => $this->session->id,
            'event_type'  => 'tab_switch',
            'metadata'    => ['count' => $this->tabSwitchCount, 'timestamp' => now()->toIso8601String()],
            'occurred_at' => now(),
        ]);

        $this->session->update(['tab_switch_count' => $this->tabSwitchCount]);

        if ($this->tabSwitchCount === 1) {
            $this->showTabWarning  = true;
            $this->tabSwitchWarning = 'Warning: Do not leave the exam page. Tab switches are being tracked.';
        }

        if ($this->tabSwitchCount >= $this->maxTabSwitches) {
            $this->session->update([
                'is_flagged'  => true,
                'flag_reason' => 'Auto-submitted: '.$this->maxTabSwitches.' tab switches detected',
            ]);
            $this->submit();
        }
    }

    public function dismissTabWarning(): void
    {
        $this->showTabWarning = false;
    }

    public function handleBlockedAction(string $message): void
    {
        $eventType = match (true) {
            str_contains($message, 'Copy')                                     => 'copy_attempt',
            str_contains($message, 'Paste')                                    => 'paste_attempt',
            str_contains($message, 'Cut')                                      => 'cut_attempt',
            str_contains($message, 'Right-click')                              => 'right_click',
            str_contains($message, 'F12'), str_contains($message, 'Developer') => 'devtools_open',
            default                                                            => 'blocked_action',
        };

        ActivityLog::create([
            'session_id'  => $this->session->id,
            'event_type'  => $eventType,
            'metadata'    => ['message' => $message, 'timestamp' => now()->toIso8601String()],
            'occurred_at' => now(),
        ]);
    }

    public function hydrate(): void
    {
        // Intentionally empty — submission status is checked via wire:poll only
    }

    public function checkSubmissionStatus(): void
    {
        $this->session->refresh();
        $this->remainingSeconds = $this->getRemainingSeconds();

        if ($this->session->is_submitted) {
            $this->dispatch('notify', ['message' => 'Your exam has been submitted by the administrator.', 'type' => 'warning']);
            $this->redirectRoute('student.results', ['session' => $this->session]);
        } elseif ($this->remainingSeconds <= 0) {
            $this->submit();
        }
    }

    public function getRemainingSeconds(): int
    {
        $deadline = $this->session->started_at->addMinutes($this->exam->duration_minutes);
        return max(0, (int) now()->diffInSeconds($deadline));
    }

    /**
     * Toggle a checkbox option for MCQ questions.
     */
    public function toggleOption(int $questionId, int $optionId): void
    {
        if ($this->session->is_submitted) {
            return;
        }

        $key            = (string) $questionId;
        $currentOptions = $this->answers[$key]['selected_options'] ?? [];

        if (in_array($optionId, $currentOptions)) {
            $currentOptions = array_values(array_filter($currentOptions, fn ($id) => $id !== $optionId));
        } else {
            $currentOptions[] = $optionId;
        }

        $this->saveAnswer($questionId, $currentOptions);
    }

    public function isOptionSelected(int $questionId, int $optionId): bool
    {
        return in_array($optionId, $this->answers[(string) $questionId]['selected_options'] ?? []);
    }

    public function saveAnswer(int $questionId, mixed $value): void
    {
        if ($this->session->is_submitted) {
            return;
        }

        // String key is critical — PHP integer array keys become strings after JSON
        // round-trip through Livewire, so we normalise upfront to avoid mismatches.
        $key  = (string) $questionId;
        $data = ['session_id' => $this->session->id, 'question_id' => $questionId];

        if (is_array($value)) {
            $filtered               = array_values(array_filter($value));
            $data['selected_option_id'] = empty($filtered) ? null : $filtered[0];
            $data['selected_options']   = $filtered;
            $this->answers[$key] = [
                'selected_option_id' => $data['selected_option_id'],
                'selected_options'   => $filtered,
                'text_answer'        => $this->answers[$key]['text_answer'] ?? null,
            ];
        } elseif (is_int($value)) {
            $data['selected_option_id'] = $value;
            $data['selected_options']   = [$value];
            $this->answers[$key] = [
                'selected_option_id' => $value,
                'selected_options'   => [$value],
                'text_answer'        => $this->answers[$key]['text_answer'] ?? null,
            ];
        } elseif (is_string($value)) {
            $data['text_answer'] = $value;
            $this->answers[$key] = [
                'selected_option_id' => $this->answers[$key]['selected_option_id'] ?? null,
                'selected_options'   => $this->answers[$key]['selected_options'] ?? [],
                'text_answer'        => $value,
            ];
        }

        Answer::updateOrCreate(
            ['session_id' => $this->session->id, 'question_id' => $questionId],
            $data
        );
    }

    public function toggleFlag(int $questionId): void
    {
        $flagged = $this->session->flagged_questions ?? [];

        if (in_array($questionId, $flagged)) {
            $flagged = array_values(array_filter($flagged, fn ($id) => $id !== $questionId));
        } else {
            $flagged[] = $questionId;
        }

        $this->session->update(['flagged_questions' => $flagged]);
    }

    public function isFlagged(int $questionId): bool
    {
        return in_array($questionId, $this->session->flagged_questions ?? []);
    }

    public function submit(): void
    {
        Log::info('[ExamTaking] submit() called', ['session_id' => $this->session->id ?? 'N/A']);

        if (! isset($this->session) || ! $this->session->exists || $this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        $this->session->update([
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $this->dispatch('notify', ['message' => 'Exam submitted successfully.', 'type' => 'success']);

        $this->redirectRoute('student.results', ['session' => $this->session]);
    }

    public function getAnsweredCount(): int
    {
        return count(array_filter($this->answers, fn ($a) =>
            ! empty($a['selected_option_id']) ||
            ! empty($a['selected_options'])   ||
            ! empty($a['text_answer'])
        ));
    }

    public function isAnswered(int $questionId): bool
    {
        $key = (string) $questionId;

        if (! isset($this->answers[$key])) {
            return false;
        }

        $a = $this->answers[$key];

        return ! empty($a['selected_option_id']) ||
               ! empty($a['selected_options'])   ||
               ! empty($a['text_answer']);
    }

    public function render()
    {
        return view('livewire.student.exam-taking', [
            'answeredCount'    => $this->getAnsweredCount(),
            'totalCount'       => $this->questions->count(),
            'tabSwitchCount'   => $this->tabSwitchCount,
            'showTabWarning'   => $this->showTabWarning,
            'tabSwitchWarning' => $this->tabSwitchWarning,
            'isIpChanged'      => $this->isIpChanged,
            'ipChangeWarning'  => $this->ipChangeWarning,
            'maxTabSwitches'   => $this->maxTabSwitches,
        ]);
    }
}