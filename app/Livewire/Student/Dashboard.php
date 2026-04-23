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
        $user = auth()->user();

        // Get exam IDs the user has already attempted
        $attemptedExamIds = $user->examSessions()->pluck('exam_id');

        // Get available published exams (not yet attempted)
        $this->exams = Exam::where('is_published', true)
            ->whereNotIn('id', $attemptedExamIds)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->withCount('questions')
            ->get();

        // Get already attempted exams (for results display)
        $this->attempted = $user->examSessions()
            ->with('exam')
            ->where('is_submitted', true)
            ->get();
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
