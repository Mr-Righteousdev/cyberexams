<?php

namespace App\Livewire\Student;

use App\Models\ExamSession;
use App\Services\GradingService;
use Livewire\Component;

class Results extends Component
{
    public ExamSession $session;

    public function mount(ExamSession $session): void
    {
        // Verify session belongs to current user (SESS-09)
        abort_unless($session->user_id === auth()->id(), 403);

        // Run grading if not yet graded
        if ($session->is_submitted && $session->score === null) {
            GradingService::grade($session);
            $session->refresh();
        }

        $this->session = $session->load(['exam', 'answers.question', 'answers.selectedOption']);
    }

    public function render()
    {
        return view('livewire.student.results');
    }
}
