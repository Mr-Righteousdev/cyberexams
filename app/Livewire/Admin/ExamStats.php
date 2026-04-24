<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\Question;
use Livewire\Component;

class ExamStats extends Component
{
    public Exam $exam;

    public array $stats = [];

    public function mount(Exam $exam)
    {
        $this->exam = $exam;
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_questions' => $this->exam->questions()->count(),
            'questions_by_type' => Question::where('exam_id', $this->exam->id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'total_marks' => $this->exam->questions()->sum('marks'),
            'total_sessions' => $this->exam->sessions()->count(),
            'completed_sessions' => $this->exam->sessions()->whereNotNull('submitted_at')->count(),
            'active_sessions' => $this->exam->sessions()->whereNull('submitted_at')->count(),
            'pass_count' => $this->exam->sessions()->whereNotNull('submitted_at')->where('passed', true)->count(),
            'fail_count' => $this->exam->sessions()->whereNotNull('submitted_at')->where('passed', false)->count(),
            'avg_score' => $this->exam->sessions()
                ->whereNotNull('submitted_at')
                ->whereNotNull('percentage')
                ->avg('percentage'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.exam-stats');
    }
}
