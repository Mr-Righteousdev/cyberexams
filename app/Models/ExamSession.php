<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'ip_address',
        'started_at',
        'submitted_at',
        'is_submitted',
        'is_flagged',
        'flag_reason',
        'tab_switch_count',
        'score',
        'total_marks',
        'total_received',
        'percentage',
        'passed',
        'flagged_questions',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'is_submitted' => 'boolean',
            'is_flagged' => 'boolean',
            'passed' => 'boolean',
            'flagged_questions' => 'array',
            'total_marks' => 'integer',
            'total_received' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'session_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
