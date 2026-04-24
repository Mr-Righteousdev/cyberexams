<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\Question;
use Livewire\Component;

class QuestionIndex extends Component
{
    public Exam $exam;

    public string $viewMode = 'cards';

    public ?string $typeFilter = null;

    public function render()
    {
        $query = $this->exam->questions()->orderBy('order');

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $questions = $query->get();

        return view('livewire.admin.question-index', compact('questions'));
    }

    public function delete(Question $question)
    {
        $question->delete();
        $this->dispatch('notify', ['message' => 'Question deleted.', 'type' => 'success']);
    }

    public function moveUp(Question $question)
    {
        $question->update(['order' => $question->order - 1]);
    }

    public function moveDown(Question $question)
    {
        $question->update(['order' => $question->order + 1]);
    }
}
