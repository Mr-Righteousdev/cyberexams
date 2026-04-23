<?php

namespace App\Livewire\Admin;

use App\Models\Answer;
use App\Services\GradingService;
use Flux\Flux;
use Livewire\Component;

class ManualGrading extends Component
{
    public Answer $answer;

    public float $marks;

    public string $feedback = '';

    public bool $graded = false;

    public function mount(Answer $answer): void
    {
        $this->answer = $answer->load(['question', 'session.exam', 'session.user']);
        $this->marks = $answer->marks_awarded ?? 0;
    }

    public function grade(): void
    {
        $this->validate([
            'marks' => "required|numeric|min:0|max:{$this->answer->question->marks}",
        ]);

        GradingService::gradeAnswer($this->answer, $this->marks);

        $this->graded = true;

        Flux::toast(variant: 'success', text: "Grade saved: {$this->marks}/{$this->answer->question->marks} marks.");
    }

    public function render()
    {
        return view('livewire.admin.manual-grading');
    }
}
