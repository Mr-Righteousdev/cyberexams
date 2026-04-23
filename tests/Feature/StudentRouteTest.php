<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents deactivated student from accessing exam routes', function () {
    $deactivated = User::factory()->create([
        'role' => 'student',
        'is_active' => false,
    ]);

    actingAs($deactivated)
        ->get('/student')
        ->assertStatus(302); // redirect to login due to deactivation
});

it('allows active student to access exam dashboard', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
    ]);

    actingAs($student)
        ->get('/student')
        ->assertStatus(200);
});
