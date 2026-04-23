<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentCreate extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public ?string $student_number = null;

    public string $manual_students = '';

    public ?string $bulk_file = null;

    public bool $is_bulk = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'student_number' => 'nullable|string|max:100',
    ];

    protected $messages = [
        'manual_students.required_if' => 'Please enter at least one student.',
    ];

    public function render()
    {
        return view('livewire.admin.student-create');
    }

    public function saveSingle()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'student_number' => $this->student_number,
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        Flux::toast(variant: 'success', text: 'Student created. Default password: password');
        $this->reset(['name', 'email', 'student_number']);
    }

    public function saveBulk()
    {
        $lines = explode("\n", trim($this->manual_students));
        $created = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = array_map('trim', explode(',', $line));
            $email = $parts[0] ?? null;

            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $name = $parts[1] ?? ucfirst(explode('@', $email)[0]);
            $studentNumber = $parts[2] ?? null;

            if (User::where('email', $email)->exists()) {
                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'student_number' => $studentNumber,
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_active' => true,
            ]);

            $created++;
        }

        if ($created > 0) {
            Flux::toast(variant: 'success', text: "Created {$created} students. Default password: password");
            $this->manual_students = '';
        }
    }
}
