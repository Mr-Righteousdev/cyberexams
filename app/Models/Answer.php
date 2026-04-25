<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'session_id',
        'question_id',
        'selected_option_id',
        'selected_options',
        'text_answer',
        'is_correct',
        'marks_awarded',
    ];

    protected function casts(): array
    {
        return [
            'selected_options' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }
}
