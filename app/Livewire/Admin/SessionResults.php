<?php

namespace App\Livewire\Admin;

use App\Models\ExamSession;
use Livewire\Component;

class SessionResults extends Component
{
    public ExamSession $session;

    public function mount(ExamSession $session): void
    {
        abort_unless($session->exam->is_published ?? false, 403);

        $this->session = $session->load([
            'exam',
            'user',
            'answers.question',
            'answers.selectedOption',
            'answers.question.options',
        ]);
    }

    public function getQuestionNumber(ExamSession $session, int $questionId): int
    {
        // Return question order number
        $index = $session->exam->questions->search(fn ($q) => $q->id === $questionId);

        return $index !== false ? $index + 1 : $questionId;
    }

    public function render()
    {
        return view('livewire.admin.session-results');
    }
}
