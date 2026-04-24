<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Flux\Flux;
use Livewire\Component;

class QuestionCreate extends Component
{
    public Exam $exam;

    public ?Question $question = null;

    public string $type = 'mcq';

    public string $question_text = '';

    public string $code_block = '';

    public string $code_language = '';

    public int $marks = 1;

    public string $explanation = '';

    public array $options = [];

    public ?int $selectedCorrect = null;

    protected function rules(): array
    {
        $rules = [
            'type' => 'required|in:mcq,true_false,short_answer,code_snippet',
            'question_text' => 'required|string',
            'code_block' => 'nullable|string',
            'code_language' => 'nullable|string|max:50',
            'marks' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
        ];

        if (in_array($this->type, ['mcq', 'code_snippet'])) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*.option_text'] = 'required|string';
        } elseif ($this->type === 'true_false') {
            $rules['selectedCorrect'] = 'required|integer';
        }

        return $rules;
    }

    public function mount(Exam $exam, ?Question $question = null)
    {
        $this->exam = $exam;
        $this->question = $question?->exam_id === $exam->id ? $question : null;

        $this->options = [];
        $this->selectedCorrect = null;

        if ($this->question) {
            $this->type = $this->question->type;
            $this->question_text = $this->question->question_text;
            $this->code_block = $this->question->code_block ?? '';
            $this->code_language = $this->question->code_language ?? '';
            $this->marks = $this->question->marks;
            $this->explanation = $this->question->explanation ?? '';

            foreach ($this->question->options as $index => $option) {
                $this->options[$index] = [
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                ];
                if ($option->is_correct && $this->type === 'true_false') {
                    $this->selectedCorrect = $index;
                }
            }
        } else {
            $this->ensureMinOptions();
        }
    }

    public function updatedType($value)
    {
        $this->options = [];
        $this->selectedCorrect = null;

        if ($value === 'true_false') {
            $this->options = [
                ['option_text' => 'True', 'is_correct' => false],
                ['option_text' => 'False', 'is_correct' => false],
            ];
            $this->selectedCorrect = 0;
        } elseif ($value === 'short_answer') {
            // No options needed
        } elseif ($value === 'code_snippet') {
            // No options shown in form
        } else {
            $this->ensureMinOptions();
        }
    }

    public function ensureMinOptions()
    {
        if (count($this->options) < 2) {
            while (count($this->options) < 2) {
                $this->options[] = ['option_text' => '', 'is_correct' => false];
            }
        }
    }

    public function addOption()
    {
        if (count($this->options) < 6) {
            $this->options[] = ['option_text' => '', 'is_correct' => false];
        }
    }

    public function removeOption($index)
    {
        if (count($this->options) > 2) {
            unset($this->options[$index]);
            $this->options = array_values($this->options);
        }
    }

    public function save()
    {
        if ($this->type === 'mcq' || $this->type === 'code_snippet') {
            $hasCorrect = collect($this->options)->contains('is_correct', true);
            if (! $hasCorrect) {
                Flux::toast(variant: 'danger', text: 'At least one option must be marked as correct.');

                return;
            }
        }

        $this->validate();

        $question = Question::create([
            'exam_id' => $this->exam->id,
            'type' => $this->type,
            'question_text' => $this->question_text,
            'code_block' => $this->code_block ?: null,
            'code_language' => $this->code_language ?: null,
            'marks' => $this->marks,
            'explanation' => $this->explanation ?: null,
            'order' => $this->exam->questions()->max('order') + 1,
        ]);

        if (in_array($this->type, ['mcq', 'true_false', 'code_snippet'])) {
            foreach ($this->options as $index => $optionData) {
                $isCorrect = $this->type === 'true_false'
                    ? ($index === $this->selectedCorrect)
                    : ($optionData['is_correct'] ?? false);

                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $isCorrect,
                    'order' => $index,
                ]);
            }
        }

        Flux::toast(variant: 'success', text: 'Question created.');

        return redirect()->route('admin.exams.questions.index', $this->exam);
    }

    public function saveAndContinue()
    {
        if ($this->type === 'mcq' || $this->type === 'code_snippet') {
            $hasCorrect = collect($this->options)->contains('is_correct', true);
            if (! $hasCorrect) {
                Flux::toast(variant: 'danger', text: 'At least one option must be marked as correct.');

                return;
            }
        }

        $this->validate();

        $question = Question::create([
            'exam_id' => $this->exam->id,
            'type' => $this->type,
            'question_text' => $this->question_text,
            'code_block' => $this->code_block ?: null,
            'code_language' => $this->code_language ?: null,
            'marks' => $this->marks,
            'explanation' => $this->explanation ?: null,
            'order' => $this->exam->questions()->max('order') + 1,
        ]);

        if (in_array($this->type, ['mcq', 'true_false', 'code_snippet'])) {
            foreach ($this->options as $index => $optionData) {
                $isCorrect = $this->type === 'true_false'
                    ? ($index === $this->selectedCorrect)
                    : ($optionData['is_correct'] ?? false);

                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $isCorrect,
                    'order' => $index,
                ]);
            }
        }

        Flux::toast(variant: 'success', text: 'Question created. You can add another.');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->question_text = '';
        $this->code_block = '';
        $this->code_language = '';
        $this->marks = 1;
        $this->explanation = '';
        $this->options = [];
        $this->selectedCorrect = null;
        $this->ensureMinOptions();
    }

    public function render()
    {
        return view('livewire.admin.question-create');
    }
}
