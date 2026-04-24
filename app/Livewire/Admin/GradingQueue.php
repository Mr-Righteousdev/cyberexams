<?php

namespace App\Livewire\Admin;

use App\Services\GradingService;
use Livewire\Component;
use Livewire\WithPagination;

class GradingQueue extends Component
{
    use WithPagination;

    public function getPendingAnswersProperty()
    {
        return GradingService::getPendingGrading();
    }

    public function getStatsProperty(): array
    {
        $pending = $this->pendingAnswers;

        return [
            'total' => $pending->count(),
            'by_exam' => $pending->groupBy('session.exam_id')->map(fn ($g) => $g->count()),
        ];
    }

    public function render()
    {
        return view('livewire.admin.grading-queue', [
            'pendingAnswers' => $this->pendingAnswers,
            'stats' => $this->stats,
        ]);
    }
}
