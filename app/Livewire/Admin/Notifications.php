<?php

namespace App\Livewire\Admin;

use App\Models\Notification;
use App\Notifications\ImportQuestionsComplete;
use Livewire\Attributes\On;
use Livewire\Component;

class Notifications extends Component
{
    public array $modalData = [];

    public bool $showModal = false;

    #[On('show-import-result')]
    public function showImportResult(array $data): void
    {
        $this->modalData = $data;
        $this->showModal = true;
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function openImportNotification(string $notificationId): void
    {
        $notification = Notification::where('id', $notificationId)
            ->where('notifiable_id', auth()->id())
            ->first();

        if ($notification && $notification->type === ImportQuestionsComplete::class) {
            $this->modalData = [
                'exam_title' => $notification->data['exam_title'] ?? '',
                'exam_id' => $notification->data['exam_id'] ?? 0,
                'total' => $notification->data['total'] ?? 0,
                'imported' => $notification->data['imported'] ?? 0,
                'skipped_duplicate' => $notification->data['skipped_duplicate'] ?? 0,
                'skipped_invalid' => $notification->data['skipped_invalid'] ?? 0,
                'by_type' => $notification->data['by_type'] ?? [],
                'failures' => $notification->data['failures'] ?? [],
            ];
            $this->showModal = true;
            $notification->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.admin.notifications', [
            'notifications' => auth()->user()->notifications()->orderByDesc('created_at')->paginate(20),
        ]);
    }
}
