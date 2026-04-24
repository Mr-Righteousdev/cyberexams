<?php

namespace App\Livewire\Student;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExamStart extends Component
{
    public Exam $exam;

    public function mount(Exam $exam): void
    {
        $user = auth()->user();

        Log::info('[ExamStart] mount() called', [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'exam_id' => $exam->id,
            'exam_title' => $exam->title,
            'exam_starts_at' => $exam->starts_at?->format('Y-m-d H:i:s'),
            'now' => now()->format('Y-m-d H:i:s'),
        ]);

        $examNotYetAvailable = $exam->starts_at && $exam->starts_at->isAfter(now());

        Log::info('[ExamStart] Check 1: exam not yet available?', [
            'starts_at' => $exam->starts_at,
            'isAfter(now)' => $exam->starts_at ? $exam->starts_at->isAfter(now()) : 'N/A (null)',
            'result' => $examNotYetAvailable,
        ]);

        if ($examNotYetAvailable) {
            Log::info('[ExamStart] REDIRECT to dashboard - exam not yet available');
            $this->redirectRoute('student.dashboard');

            return;
        }

        $existingSubmittedSession = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('is_submitted', true)
            ->first();

        Log::info('[ExamStart] Check 2: existing submitted session?', [
            'found' => $existingSubmittedSession ? true : false,
            'session_id' => $existingSubmittedSession?->id,
            'submitted_at' => $existingSubmittedSession?->submitted_at?->format('Y-m-d H:i:s'),
        ]);

        if ($existingSubmittedSession) {
            Log::info('[ExamStart] REDIRECT to results - already submitted');
            $this->redirectRoute('student.results', ['session' => $existingSubmittedSession]);

            return;
        }

        $this->exam = $exam->loadCount('questions');

        Log::info('[ExamStart] mount() completed successfully - showing start page', [
            'exam_id' => $this->exam->id,
            'questions_count' => $this->exam->questions_count,
        ]);
    }

    public function start(): ?RedirectResponse
    {
        $user = auth()->user();

        Log::info('[ExamStart] start() called', [
            'user_id' => $user?->id,
            'exam_id' => $this->exam->id ?? 'N/A',
        ]);

        if (! $user) {
            Log::warning('[ExamStart] start() - no user, returning null');

            return null;
        }

        if (! isset($this->exam) || ! $this->exam->exists) {
            Log::warning('[ExamStart] start() - exam not loaded, returning null');

            return null;
        }

        $existingInProgressSession = ExamSession::where('exam_id', $this->exam->id)
            ->where('user_id', $user->id)
            ->where('is_submitted', false)
            ->first();

        Log::info('[ExamStart] Check 3: existing in-progress session?', [
            'found' => $existingInProgressSession ? true : false,
            'session_id' => $existingInProgressSession?->id,
        ]);

        if ($existingInProgressSession) {
            Log::info('[ExamStart] REDIRECT to existing session');

            return $this->redirectRoute('student.exam.session', ['session' => $existingInProgressSession]);
        }

        $session = ExamSession::create([
            'exam_id' => $this->exam->id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'started_at' => now(),
            'is_submitted' => false,
        ]);

        Log::info('[ExamStart] Created new session, REDIRECT to exam taking', [
            'session_id' => $session->id,
            'exam_id' => $this->exam->id,
        ]);

        return $this->redirectRoute('student.exam.session', ['session' => $session]);
    }

    public function render()
    {
        return view('livewire.student.exam-start');
    }
}
