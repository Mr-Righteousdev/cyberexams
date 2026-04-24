<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\ExamSession;
use Livewire\Component;

class LiveMonitor extends Component
{
    public array $activeSessions = [];

    public int $refreshInterval = 10; // seconds

    public string $lastUpdate = '';

    public int $totalActive = 0;

    public function mount(): void
    {
        $this->refreshActiveSessions();
    }

    public function refreshActiveSessions(): void
    {
        $sessions = ExamSession::where('is_submitted', false)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->with(['exam', 'user', 'answers'])
            ->get()
            ->map(fn ($session) => $this->formatSession($session))
            ->toArray();

        $this->activeSessions = $sessions;
        $this->totalActive = count($sessions);
        $this->lastUpdate = now()->format('H:i:s');
    }

    public function getActiveExamProperty(): ?Exam
    {
        // Get the exam with most active sessions, or any currently running exam
        $activeSessions = ExamSession::where('is_submitted', false)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->whereHas('exam', fn ($q) => $q->where('is_published', true))
            ->with('exam')
            ->get();

        if ($activeSessions->isEmpty()) {
            return null;
        }

        // Return the exam that's currently being taken
        return $activeSessions->first()->exam;
    }

    private function formatSession(ExamSession $session): array
    {
        $exam = $session->exam;
        $durationMinutes = $exam->duration_minutes ?? 60;

        // Calculate time remaining
        $timeRemaining = '—';
        if ($session->started_at) {
            $expiresAt = $session->started_at->addMinutes($durationMinutes);
            if ($expiresAt->isFuture()) {
                $minutes = now()->diffInMinutes($expiresAt);
                $seconds = now()->diffInSeconds($expiresAt) % 60;
                $timeRemaining = "{$minutes}:{$seconds}";
            } else {
                $timeRemaining = 'EXPIRED';
            }
        }

        // Count answered questions
        $answeredCount = $session->answers()->count();
        $totalQuestions = $exam->questions()->count();
        $progressPercent = $totalQuestions > 0
            ? round(($answeredCount / $totalQuestions) * 100)
            : 0;

        // Check for tab switch issues
        $tabSwitchCount = $session->tab_switch_count ?? 0;
        $flaggedQuestions = $session->flagged_questions ?? [];

        return [
            'id' => $session->id,
            'student_name' => $session->user->name,
            'student_email' => $session->user->email,
            'exam_title' => $exam->title,
            'time_remaining' => $timeRemaining,
            'answers_count' => $answeredCount,
            'total_questions' => $totalQuestions,
            'progress_percent' => $progressPercent,
            'tab_switch_count' => $tabSwitchCount,
            'flagged_questions' => $flaggedQuestions,
            'started_at' => $session->started_at?->format('H:i:s'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.live-monitor');
    }
}
