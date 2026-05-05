<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class ExamIndex extends Component
{
    use WithPagination;

    public string $viewMode = 'cards';

    public ?int $examToDelete = null;

    public function confirmDelete(int $examId): void
    {
        $this->examToDelete = $examId;
        $this->modal('delete-exam')->show();
    }

    public function deleteExam(): void
    {
        Exam::findOrFail($this->examToDelete)->delete();

        $this->examToDelete = null;
        $this->modal('delete-exam')->close();

        // optional toast
        Flux::toast('Exam deleted successfully.', variant: 'danger');
    }

    public function render()
    {
        $exams = Exam::latest()->paginate(10);

        return view('livewire.admin.exam-index', compact('exams'));
    }

    public function delete(Exam $exam)
    {
        $exam->delete();

        Flux::toast(variant: 'success', text: 'Exam deleted successfully.');
    }

    public function togglePublish(Exam $exam)
    {
        $exam->update(['is_published' => ! $exam->is_published]);

        $status = $exam->is_published ? 'published' : 'unpublished';
        Flux::toast(variant: 'success', text: "Exam {$status} successfully.");
    }
}
