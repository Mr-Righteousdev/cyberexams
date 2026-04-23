<?php

namespace App\Models;

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
        'passing_marks',
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
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
