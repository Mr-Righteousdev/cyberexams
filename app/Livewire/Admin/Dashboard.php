<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Services\AnalyticsService;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];

    public array $exams = [];

    public function mount(): void
    {
        $this->stats = AnalyticsService::getDashboardStats();

        // Load exams with session counts and pass rates
        $this->exams = Exam::withCount(['sessions' => fn ($q) => $q->where('is_submitted', true)])
            ->with(['sessions' => fn ($q) => $q->where('is_submitted', true)->whereNotNull('score')])
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'session_count' => $exam->sessions_count,
                'avg_score' => $exam->sessions->avg('percentage') ?? 0,
                'pass_rate' => $this->calcPassRate($exam->sessions),
                'question_count' => $exam->questions()->count(),
            ])
            ->toArray();
    }

    private function calcPassRate($sessions): float
    {
        if ($sessions->isEmpty()) {
            return 0;
        }
        $passed = $sessions->where('passed', true)->count();

        return round(($passed / $sessions->count()) * 100, 1);
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
