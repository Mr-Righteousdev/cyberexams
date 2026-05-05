<?php

namespace App\Livewire\Student;

use App\Models\Exam;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public Collection $exams;

    public Collection $attempted;

    public function mount(): void
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->exams = collect();
                $this->attempted = collect();

                return;
            }

            $attemptedExamIds = $user->examSessions()->pluck('exam_id');

            $openExams = Exam::where('is_published', true)
                ->where('access_mode', 'open')
                ->whereNotIn('id', $attemptedExamIds)
                ->where(function ($q) {
                    $q->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->pluck('id');

            $assignedExams = Exam::where('is_published', true)
                ->where('access_mode', 'restricted')
                ->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                ->whereNotIn('id', $attemptedExamIds)
                ->where(function ($q) {
                    $q->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->pluck('id');

            $availableExamIds = $openExams->merge($assignedExams)->unique();

            $this->exams = Exam::whereIn('id', $availableExamIds)
                ->withCount('questions')
                ->get();

            $this->attempted = $user->examSessions()
                ->with('exam')
                ->where('is_submitted', true)
                ->get()
                ->filter(function ($session) {
                    return $session->exam->allow_repeat_after_fail
                        || $session->passed === true
                        || $session->passed === null;
                });
        } catch (\Throwable $e) {
            $this->exams = collect();
            $this->attempted = collect();
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
