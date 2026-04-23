<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use Livewire\Component;
use Livewire\WithPagination;

class ExamIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $exams = Exam::latest()->paginate(10);

        return view('livewire.admin.exam-index', compact('exams'));
    }

    public function delete(Exam $exam)
    {
        $exam->delete();

        $this->dispatch('notify', ['message' => 'Exam deleted successfully.', 'type' => 'success']);
    }

    public function togglePublish(Exam $exam)
    {
        $exam->update(['is_published' => ! $exam->is_published]);

        $status = $exam->is_published ? 'published' : 'unpublished';
        $this->dispatch('notify', ["Exam {$status} successfully.", 'type' => 'success']);
    }
}
