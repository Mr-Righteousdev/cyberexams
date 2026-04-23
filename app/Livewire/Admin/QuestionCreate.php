<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
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

    protected $rules = [
        'type' => 'required|in:mcq,true_false,short_answer,code_snippet',
        'question_text' => 'required|string',
        'code_block' => 'nullable|string',
        'code_language' => 'nullable|string|max:50',
        'marks' => 'required|integer|min:1',
        'explanation' => 'nullable|string',
        'options' => 'required|array|min:2',
        'options.*.option_text' => 'required|string',
    ];

    public function mount(Exam $exam, ?Question $question = null)
    {
        $this->exam = $exam;
        $this->question = $question?->exam_id === $exam->id ? $question : null;

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
            }
        }
    }

    public function updatedType($value)
    {
        if ($value === 'true_false') {
            $this->options = [
                ['option_text' => 'True', 'is_correct' => false],
                ['option_text' => 'False', 'is_correct' => false],
            ];
        } elseif ($value === 'short_answer' || $value === 'code_snippet') {
            $this->options = [];
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
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'order' => $index,
                ]);
            }
        }

        $this->dispatch('notify', ['message' => 'Question created.', 'type' => 'success']);

        return redirect()->route('admin.exams.questions.index', $this->exam);
    }

    public function render()
    {
        return view('livewire.admin.question-create');
    }
}
