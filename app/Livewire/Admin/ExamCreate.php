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

    // Total marks target is a required field (per-exam static cap)
    public int $total_marks_target = 0;

    // Admin-readout of pool total marks for the current exam context
    public int $poolTotalMarks = 0;

    public ?int $passing_percentage = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public bool $shuffle_questions = true;

    public bool $shuffle_options = true;

    public bool $is_published = false;

    public string $access_mode = 'open';

    public bool $allow_repeat_after_fail = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'instructions' => 'nullable|string',
        'duration_minutes' => 'required|integer|min:1',
        'total_marks' => 'nullable|integer|min:0',
        'total_marks_target' => 'required|integer|min:0',
        'passing_percentage' => 'nullable|integer|min:0|max:100',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date|after:starts_at',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_published' => 'boolean',
        'access_mode' => 'required|in:open,restricted',
        'allow_repeat_after_fail' => 'boolean',
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
            $this->total_marks_target = $exam->total_marks_target ?? 0;
            $this->passing_percentage = $exam->passing_percentage;
            $this->starts_at = $exam->starts_at;
            $this->ends_at = $exam->ends_at?->format('Y-m-d H:i');
            $this->shuffle_questions = $exam->shuffle_questions;
            $this->shuffle_options = $exam->shuffle_options;
            $this->is_published = $exam->is_published;
            $this->access_mode = $exam->access_mode ?? 'open';
            $this->allow_repeat_after_fail = $exam->allow_repeat_after_fail ?? false;
            // Pool total marks for the existing questions
            $this->poolTotalMarks = $exam->questions()->sum('marks');
        }

        // dd($this->starts_at);
    }

    public function save()
    {
        $this->validate();

        if ($this->is_published && $this->exam) {
            $check = $this->exam->canBePublished();
            if (! $check['can_publish']) {
                $reason = match ($check['reason']) {
                    'no_questions' => 'Exam has no questions yet.',
                    'question_pool_insufficient' => "Question pool has {$check['pool_marks']} marks but target is {$check['target_marks']} marks. Add more questions.",
                    'no_assignments' => 'Restricted exam must have at least one student assigned.',
                    default => 'Cannot publish exam.',
                };
                Flux::toast(variant: 'danger', text: $reason);

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
        $data['access_mode'] = $this->access_mode;
        $data['allow_repeat_after_fail'] = $this->allow_repeat_after_fail;

        // Post-validation: ensure target does not exceed the pool of questions
        $poolMarks = 0;
        if ($this->exam) {
            $poolMarks = $this->exam->questions()->sum('marks');
        }

        if ($poolMarks > 0 && $this->total_marks_target > $poolMarks) {
            $this->addError('total_marks_target', 'Total target cannot exceed pool marks ('.$poolMarks.')');

            return;
        }

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

    // Computed pool total marks for readouts in the admin UI
    public function getPoolTotalMarksProperty(): int
    {
        return $this->exam ? $this->exam->questions()->sum('marks') : 0;
    }
}
