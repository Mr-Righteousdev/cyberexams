<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class StudentIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $students = User::where('role', 'student')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.student-index', compact('students'));
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->fresh()->is_active ? 'activated' : 'deactivated';
        $this->dispatch('notify', ["Student {$status}.", 'type' => 'success']);
    }

    public function resetPassword(User $user)
    {
        $user->update(['password' => bcrypt('password')]);
        $this->dispatch('notify', ['Password reset to "password".', 'type' => 'success']);
    }

    public function delete(User $user)
    {
        $user->delete();
        $this->dispatch('notify', ['Student deleted.', 'type' => 'success']);
    }
}
