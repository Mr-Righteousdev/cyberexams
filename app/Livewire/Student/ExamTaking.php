<?php

namespace App\Livewire\Student;

use App\Models\Answer;
use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExamTaking extends Component
{
    public ExamSession $session;

    public Exam $exam;

    public Collection $questions;

    public int $currentIndex = 0;

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
        $this->session = $session;
        $this->exam = $session->exam;

        // Load tab switch count from session
        $this->tabSwitchCount = $session->tab_switch_count ?? 0;
        $this->maxTabSwitches = $this->exam->max_tab_switches ?? 3;

        // IP check with change detection (GUARD-06)
        $this->checkIpChange();

        // Already submitted check (SESS-08)
        if ($session->is_submitted) {
            $this->redirectRoute('student.results', ['session' => $session]);

            return;
        }

        // Load questions (with shuffle if enabled)
        $query = $this->exam->questions()->with('options');
        if ($this->exam->shuffle_questions) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('order');
        }
        $this->questions = $query->get();

        // Compute remaining time (SESS-06)
        $this->remainingSeconds = $this->getRemainingSeconds();

        // Auto-submit if time is up
        if ($this->remainingSeconds <= 0) {
            $this->submit();

            return;
        }

        // Load existing answers
        foreach ($session->answers as $answer) {
            $this->answers[$answer->question_id] = [
                'selected_option_id' => $answer->selected_option_id,
                'text_answer' => $answer->text_answer,
            ];
        }
    }

    protected function checkIpChange(): void
    {
        $currentIp = request()->ip();
        $originalIp = $this->session->ip_address;

        if ($originalIp !== $currentIp) {
            // IP has changed - log and flag
            ActivityLog::create([
                'session_id' => $this->session->id,
                'event_type' => 'ip_change',
                'metadata' => [
                    'original_ip' => $originalIp,
                    'new_ip' => $currentIp,
                    'changed_at' => now()->toIso8601String(),
                ],
                'occurred_at' => now(),
            ]);

            // Flag but don't block
            $this->session->update([
                'is_flagged' => true,
                'flag_reason' => 'IP address changed: ' . $originalIp . ' -> ' . $currentIp,
            ]);

            $this->isIpChanged = true;
            $this->ipChangeWarning = 'Your session has been flagged due to network changes during the exam.';
        }
    }

    public function handleTabSwitch(): void
    {
        if ($this->session->is_submitted) {
            return;
        }

        $this->tabSwitchCount = ($this->tabSwitchCount ?? 0) + 1;

        // Log to activity_logs
        ActivityLog::create([
            'session_id' => $this->session->id,
            'event_type' => 'tab_switch',
            'metadata' => [
                'count' => $this->tabSwitchCount,
                'timestamp' => now()->toIso8601String(),
            ],
            'occurred_at' => now(),
        ]);

        // Update session tab_switch_count
        $this->session->update(['tab_switch_count' => $this->tabSwitchCount]);

        // Show warning after 1st switch
        if ($this->tabSwitchCount === 1) {
            $this->showTabWarning = true;
            $this->tabSwitchWarning = 'Warning: Do not leave the exam page. Tab switches are being tracked.';
        }

        // Auto-submit after max switches
        if ($this->tabSwitchCount >= $this->maxTabSwitches) {
            $this->session->update([
                'is_flagged' => true,
                'flag_reason' => 'Auto-submitted: ' . $this->maxTabSwitches . ' tab switches detected',
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
        // Log blocked action attempt
        $eventType = match (true) {
            str_contains($message, 'Copy') => 'copy_attempt',
            str_contains($message, 'Paste') => 'paste_attempt',
            str_contains($message, 'Cut') => 'cut_attempt',
            str_contains($message, 'Right-click') => 'right_click',
            str_contains($message, 'F12'), str_contains($message, 'Developer') => 'devtools_open',
            default => 'blocked_action',
        };

        ActivityLog::create([
            'session_id' => $this->session->id,
            'event_type' => $eventType,
            'metadata' => [
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ],
            'occurred_at' => now(),
        ]);
    }

    public function hydrate(): void
    {
        $this->remainingSeconds = $this->getRemainingSeconds();

        if ($this->remainingSeconds <= 0 && ! $this->session->is_submitted) {
            $this->submit();
        }
    }

    public function getRemainingSeconds(): int
    {
        $deadline = $this->session->started_at->addMinutes($this->exam->duration_minutes);

        return max(0, (int) $deadline->diffInSeconds(now()));
    }

    public function getCurrentQuestion(): Question
    {
        return $this->questions[$this->currentIndex];
    }

    public function saveAnswer(int $questionId, mixed $value): void
    {
        if ($this->session->is_submitted) {
            return;
        }

        $data = [
            'session_id' => $this->session->id,
            'question_id' => $questionId,
        ];

        if (is_int($value)) {
            $data['selected_option_id'] = $value;
        } elseif (is_string($value)) {
            $data['text_answer'] = $value;
        }

        Answer::updateOrCreate(
            ['session_id' => $this->session->id, 'question_id' => $questionId],
            $data
        );

        // Update local array
        if (! isset($this->answers[$questionId])) {
            $this->answers[$questionId] = [
                'selected_option_id' => null,
                'text_answer' => null,
            ];
        }

        if (is_int($value)) {
            $this->answers[$questionId]['selected_option_id'] = $value;
        } elseif (is_string($value)) {
            $this->answers[$questionId]['text_answer'] = $value;
        }
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
        $flagged = $this->session->flagged_questions ?? [];

        return in_array($questionId, $flagged);
    }

    public function navigate(string $direction): void
    {
        if ($direction === 'next' && $this->currentIndex < count($this->questions) - 1) {
            $this->currentIndex++;
        } elseif ($direction === 'prev' && $this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function goToQuestion(int $index): void
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->currentIndex = $index;
        }
    }

    public function submit(): RedirectResponse
    {
        if ($this->isSubmitting) {
            return $this->redirectRoute('student.results', ['session' => $this->session]);
        }

        $this->isSubmitting = true;

        $this->session->update([
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $this->dispatch('notify', ['message' => 'Exam submitted successfully.', 'type' => 'success']);

        return $this->redirectRoute('student.results', ['session' => $this->session]);
    }

    public function getAnsweredCount(): int
    {
        return count(array_filter($this->answers, fn ($a) => ! empty($a['selected_option_id']) || ! empty($a['text_answer'])));
    }

    public function isAnswered(int $questionId): bool
    {
        if (! isset($this->answers[$questionId])) {
            return false;
        }

        $answer = $this->answers[$questionId];

        return ! empty($answer['selected_option_id']) || ! empty($answer['text_answer']);
    }

    public function render()
    {
        return view('livewire.student.exam-taking', [
            'tabSwitchCount' => $this->tabSwitchCount,
            'showTabWarning' => $this->showTabWarning,
            'tabSwitchWarning' => $this->tabSwitchWarning,
            'isIpChanged' => $this->isIpChanged,
            'ipChangeWarning' => $this->ipChangeWarning,
            'maxTabSwitches' => $this->maxTabSwitches,
        ]);
    }
}
