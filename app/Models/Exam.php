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
        'access_mode',
        'allow_repeat_after_fail',
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
            'allow_repeat_after_fail' => 'boolean',
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

    public function assignments(): HasMany
    {
        return $this->hasMany(ExamAssignment::class);
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

        if ($this->access_mode === 'restricted' && $this->assignments()->count() === 0) {
            return [
                'can_publish' => false,
                'reason' => 'no_assignments',
                'pool_marks' => $poolMarks,
                'target_marks' => $target,
            ];
        }

        return ['can_publish' => true];
    }

    public function selectQuestionsForStudent(): Collection
    {
        $target = $this->total_marks_target ?? $this->questions()->sum('marks');
        $allQuestions = $this->questions()->with('options')->get()->shuffle();
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

    public function isAssignedTo(int $userId): bool
    {
        if ($this->access_mode === 'open') {
            return true;
        }

        return $this->assignments()->where('user_id', $userId)->exists();
    }

    public function canStudentRetake(int $userId): bool
    {
        if ($this->allow_repeat_after_fail) {
            return true;
        }

        return ! $this->sessions()
            ->where('user_id', $userId)
            ->where('is_submitted', true)
            ->exists();
    }
}
