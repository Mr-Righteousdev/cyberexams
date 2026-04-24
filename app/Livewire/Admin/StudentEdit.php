<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class StudentEdit extends Component
{
    public User $student;

    public string $name = '';

    public string $email = '';

    public ?string $student_number = null;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->student->id,
            'student_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function mount(User $student)
    {
        $this->student = $student;
        $this->name = $student->name;
        $this->email = $student->email;
        $this->student_number = $student->student_number;
        $this->is_active = $student->is_active;
    }

    public function save()
    {
        $this->validate();

        $this->student->update([
            'name' => $this->name,
            'email' => $this->email,
            'student_number' => $this->student_number,
            'is_active' => $this->is_active,
        ]);

        Flux::toast(variant: 'success', text: 'Student updated successfully.');
    }

    public function resetPassword()
    {
        $this->student->update(['password' => Hash::make('password')]);
        Flux::toast(variant: 'success', text: 'Password reset to "password".');
    }

    public function render()
    {
        return view('livewire.admin.student-edit');
    }
}
