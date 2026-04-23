<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use Carbon\Carbon;
use Livewire\Component;

class ExamCreate extends Component
{
    public ?Exam $exam = null;

    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public int $duration_minutes = 60;

    public int $total_marks = 0;

    public ?int $passing_marks = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public bool $shuffle_questions = true;

    public bool $shuffle_options = true;

    public bool $is_published = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'instructions' => 'nullable|string',
        'duration_minutes' => 'required|integer|min:1',
        'total_marks' => 'nullable|integer|min:0',
        'passing_marks' => 'nullable|integer|min:0',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date|after:starts_at',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function mount(?Exam $exam = null)
    {
        if ($exam && $exam->exists) {
            $this->exam = $exam;
            $this->title = $exam->title;
            $this->description = $exam->description ?? '';
            $this->instructions = $exam->instructions ?? '';
            $this->duration_minutes = $exam->duration_minutes;
            $this->total_marks = $exam->total_marks;
            $this->passing_marks = $exam->passing_marks;
            $this->starts_at = $exam->starts_at?->format('Y-m-d H:i');
            $this->ends_at = $exam->ends_at?->format('Y-m-d H:i');
            $this->shuffle_questions = $exam->shuffle_questions;
            $this->shuffle_options = $exam->shuffle_options;
            $this->is_published = $exam->is_published;
        }
    }

    public function save()
    {
        $this->validate();

        $data = $this->only([
            'title',
            'description',
            'instructions',
            'duration_minutes',
            'total_marks',
            'passing_marks',
            'starts_at',
            'ends_at',
            'shuffle_questions',
            'shuffle_options',
            'is_published',
        ]);

        $data['starts_at'] = $data['starts_at'] ? Carbon::parse($data['starts_at']) : null;
        $data['ends_at'] = $data['ends_at'] ? Carbon::parse($data['ends_at']) : null;

        if ($this->exam) {
            $this->exam->update($data);
            $message = 'Exam updated successfully.';
        } else {
            Exam::create($data);
            $message = 'Exam created successfully.';
        }

        session()->flash('success', $message);

        return redirect()->route('admin.exams.index');
    }

    public function render()
    {
        return view('livewire.admin.exam-create');
    }
}
