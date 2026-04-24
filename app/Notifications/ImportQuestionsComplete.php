<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportQuestionsComplete extends Notification
{
    use Queueable;

    public function __construct(
        public Exam $exam,
        public array $results
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exam_id' => $this->exam->id,
            'exam_title' => $this->exam->title,
            'total' => $this->results['total'],
            'imported' => $this->results['imported'],
            'skipped_duplicate' => $this->results['skipped_duplicate'],
            'skipped_invalid' => $this->results['skipped_invalid'],
            'by_type' => $this->results['by_type'],
            'failures' => $this->results['failures'],
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'import_complete';
    }
}
