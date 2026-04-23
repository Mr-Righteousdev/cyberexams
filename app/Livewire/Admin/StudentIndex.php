<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
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
        Flux::toast(variant: 'success', text: "Student {$status}.");
    }

    public function resetPassword(User $user)
    {
        $user->update(['password' => bcrypt('password')]);
        Flux::toast(variant: 'success', text: 'Password reset to "password".');
    }

    public function delete(User $user)
    {
        $user->delete();
        Flux::toast(variant: 'success', text: 'Student deleted.');
    }
}
