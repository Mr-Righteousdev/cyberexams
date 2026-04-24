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

            // dd(now());

            $attemptedExamIds = $user->examSessions()->pluck('exam_id');

            $this->exams = Exam::where('is_published', true)
                ->whereNotIn('id', $attemptedExamIds)
                ->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('starts_at', '>=', now())
                            ->where('starts_at', '<=', now()->addHours(2));
                    })
                        ->orWhereNull('starts_at');
                })
                ->where(function ($q) {
                    $q->whereNull('starts_at') // open anytime
                        ->orWhere('starts_at', '<=', now()); // has started (now or in the past)
                })
                ->withCount('questions')
                ->get();

            $this->attempted = $user->examSessions()
                ->with('exam')
                ->where('is_submitted', true)
                ->get();
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
