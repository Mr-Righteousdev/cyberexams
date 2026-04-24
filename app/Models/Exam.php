<?php

namespace App\Models;

use App\Casts\EatDatetime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'total_marks',
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
}
