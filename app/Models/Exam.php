<?php

namespace App\Models;

use App\Casts\EatDatetime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'total_marks',
        'total_marks_target',
        'passing_percentage',
        'starts_at',
        'ends_at',
        'shuffle_questions',
        'shuffle_options',
        'is_published',
        'max_tab_switches',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => EatDatetime::class,
            'ends_at' => EatDatetime::class,
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'is_published' => 'boolean',
            'max_tab_switches' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function canBePublished(): array
    {
        $poolMarks = $this->questions()->sum('marks');
        $target = $this->total_marks_target ?? $poolMarks;

        if ($target > 0 && $poolMarks < $target) {
            return [
                'can_publish' => false,
                'reason' => 'question_pool_insufficient',
                'pool_marks' => $poolMarks,
                'target_marks' => $target,
            ];
        }

        if ($this->questions()->count() === 0) {
            return [
                'can_publish' => false,
                'reason' => 'no_questions',
                'pool_marks' => 0,
                'target_marks' => $target,
            ];
        }

        return ['can_publish' => true];
    }

    public function selectQuestionsForStudent(): Collection
    {
        $target = $this->total_marks_target ?? $this->questions()->sum('marks');
        $allQuestions = $this->questions()->get()->shuffle();
        $selected = collect();
        $currentTotal = 0;

        foreach ($allQuestions as $question) {
            if ($currentTotal + $question->marks <= $target) {
                $selected->push($question);
                $currentTotal += $question->marks;
            }
        }

        return $selected;
    }

    public function getActualMarksAttribute(): int
    {
        return $this->total_marks_target
            ? min($this->total_marks_target, $this->questions()->sum('marks'))
            : $this->questions()->sum('marks');
    }
}
