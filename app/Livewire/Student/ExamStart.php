<?php

namespace App\Livewire\Student;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;

class ExamStart extends Component
{
    public Exam $exam;

    public function mount(Exam $exam): void
    {
        $user = auth()->user();

        // Check if user already has a submitted session for this exam
        $existingSession = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('is_submitted', true)
            ->first();

        if ($existingSession) {
            $this->redirectRoute('student.results', ['session' => $existingSession]);

            return;
        }

        // Load exam with question count
        $this->exam = $exam->loadCount('questions');
    }

    public function start(): RedirectResponse
    {
        $user = auth()->user();

        // Check for existing in-progress session
        $existingSession = ExamSession::where('exam_id', $this->exam->id)
            ->where('user_id', $user->id)
            ->where('is_submitted', false)
            ->first();

        if ($existingSession) {
            return $this->redirectRoute('student.exam.session', ['session' => $existingSession]);
        }

        // Create new session
        $session = ExamSession::create([
            'exam_id' => $this->exam->id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'started_at' => now(),
            'is_submitted' => false,
        ]);

        return $this->redirectRoute('student.exam.session', ['session' => $session]);
    }

    public function render()
    {
        return view('livewire.student.exam-start');
    }
}
