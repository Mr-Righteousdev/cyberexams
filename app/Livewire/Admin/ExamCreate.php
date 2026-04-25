<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use Flux\Flux;
use Livewire\Component;

class ExamCreate extends Component
{
    public ?Exam $exam = null;

    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public int $duration_minutes = 60;

    public int $total_marks = 0;

    public ?int $total_marks_target = null;

    public ?int $passing_percentage = null;

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
        'total_marks_target' => 'nullable|integer|min:1',
        'passing_percentage' => 'nullable|integer|min:0|max:100',
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
            $this->total_marks_target = $exam->total_marks_target;
            $this->passing_percentage = $exam->passing_percentage;
            $this->starts_at = $exam->starts_at;
            $this->ends_at = $exam->ends_at?->format('Y-m-d H:i');
            $this->shuffle_questions = $exam->shuffle_questions;
            $this->shuffle_options = $exam->shuffle_options;
            $this->is_published = $exam->is_published;
        }

        // dd($this->starts_at);
    }

    public function save()
    {
        $this->validate();

        if ($this->is_published && $this->exam) {
            $check = $this->exam->canBePublished();
            if (! $check['can_publish']) {
                Flux::toast(
                    variant: 'danger',
                    text: "Cannot publish: Question pool has {$check['pool_marks']} marks but target is {$check['target_marks']} marks. Add more questions."
                );

                return;
            }
        }

        $data = $this->only([
            'title',
            'description',
            'instructions',
            'duration_minutes',
            'passing_percentage',
            'starts_at',
            'ends_at',
            'shuffle_questions',
            'shuffle_options',
            'is_published',
        ]);

        $data['total_marks'] = $this->total_marks;
        $data['total_marks_target'] = $this->total_marks_target;

        if ($this->exam) {
            $this->exam->update($data);
            Flux::toast(variant: 'success', text: 'Exam updated successfully.');
        } else {
            Exam::create($data);
            Flux::toast(variant: 'success', text: 'Exam created successfully.');
        }

        return redirect()->route('admin.exams.index');
    }

    public function render()
    {
        return view('livewire.admin.exam-create');
    }
}
