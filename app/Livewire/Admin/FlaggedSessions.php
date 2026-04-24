<?php

namespace App\Livewire\Admin;

use App\Models\ExamSession;
use Livewire\Component;
use Livewire\WithPagination;

class FlaggedSessions extends Component
{
    use WithPagination;

    public function getFlaggedSessionsProperty()
    {
        return ExamSession::where('is_flagged', true)
            ->with(['exam', 'user'])
            ->orderByDesc('submitted_at')
            ->paginate(20);
    }

    public function clearFlag(ExamSession $session): void
    {
        $session->update([
            'is_flagged' => false,
            'flag_reason' => null,
        ]);

        $this->dispatch('notify', ['Flag cleared.', 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.flagged-sessions', [
            'flaggedSessions' => $this->flaggedSessions,
        ]);
    }
}
