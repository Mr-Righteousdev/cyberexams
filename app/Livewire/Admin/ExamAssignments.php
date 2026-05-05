<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExamAssignments extends Component
{
    public Exam $exam;

    public string $accessMode = 'open';

    public bool $allowRepeatAfterFail = false;

    public Collection $students;

    public array $selectedStudents = [];

    public $csvFile = null;

    public int $assignmentCount = 0;

    public function mount(Exam $exam)
    {
        $this->exam = $exam;
        $this->accessMode = $exam->access_mode ?? 'open';
        $this->allowRepeatAfterFail = $exam->allow_repeat_after_fail ?? false;
        $this->students = User::where('role', 'student')->orderBy('name')->get();
        $this->selectedStudents = $exam->assignments()->pluck('user_id')->toArray();
        $this->assignmentCount = $exam->assignments()->count();
    }

    public function saveAccessMode()
    {
        $this->exam->update([
            'access_mode' => $this->accessMode,
            'allow_repeat_after_fail' => $this->allowRepeatAfterFail,
        ]);

        Flux::toast(variant: 'success', text: 'Access mode updated.');
    }

    public function toggleStudent(int $userId)
    {
        if (in_array($userId, $this->selectedStudents)) {
            $this->selectedStudents = array_values(array_filter($this->selectedStudents, fn ($id) => $id !== $userId));
            ExamAssignment::where('exam_id', $this->exam->id)->where('user_id', $userId)->delete();
        } else {
            $this->selectedStudents[] = $userId;
            ExamAssignment::create([
                'exam_id' => $this->exam->id,
                'user_id' => $userId,
            ]);
        }

        $this->assignmentCount = count($this->selectedStudents);
    }

    public function selectAll()
    {
        $this->selectedStudents = $this->students->pluck('id')->toArray();

        foreach ($this->selectedStudents as $userId) {
            ExamAssignment::firstOrCreate([
                'exam_id' => $this->exam->id,
                'user_id' => $userId,
            ]);
        }

        $this->assignmentCount = count($this->selectedStudents);

        Flux::toast(variant: 'success', text: 'All students selected.');
    }

    public function clearAll()
    {
        $this->selectedStudents = [];
        ExamAssignment::where('exam_id', $this->exam->id)->delete();
        $this->assignmentCount = 0;

        Flux::toast(variant: 'success', text: 'All assignments cleared.');
    }

    public function uploadCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:512',
        ]);

        $handle = fopen($this->csvFile->getRealPath(), 'r');
        $skipped = 0;
        $added = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $studentNumber = trim($data[0]);

            if (empty($studentNumber)) {
                continue;
            }

            $user = User::where('role', 'student')
                ->where('student_number', $studentNumber)
                ->first();

            if (! $user) {
                $skipped++;

                continue;
            }

            if (! in_array($user->id, $this->selectedStudents)) {
                $this->selectedStudents[] = $user->id;
                ExamAssignment::firstOrCreate([
                    'exam_id' => $this->exam->id,
                    'user_id' => $user->id,
                ]);
                $added++;
            }
        }

        fclose($handle);
        $this->assignmentCount = count($this->selectedStudents);

        $msg = "Added {$added} students.";
        if ($skipped > 0) {
            $msg .= " Skipped {$skipped} (not found).";
        }

        Flux::toast(variant: 'success', text: $msg);
        $this->csvFile = null;
    }

    public function render()
    {
        return view('livewire.admin.exam-assignment');
    }
}
