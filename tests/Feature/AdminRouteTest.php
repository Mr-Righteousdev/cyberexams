<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents student from accessing admin routes', function () {
    $student = User::factory()->create(['role' => 'student']);

    $adminRoutes = [
        '/admin',
        '/admin/exams',
        '/admin/students',
    ];

    foreach ($adminRoutes as $route) {
        actingAs($student)
            ->get($route)
            ->assertStatus(403);
    }
});

it('allows admin to access admin routes', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    actingAs($admin)
        ->get('/admin')
        ->assertStatus(200);
});

it('redirects unauthenticated users from protected routes', function () {
    $publicAdminRoutes = ['/admin/exams', '/admin/students'];

    foreach ($publicAdminRoutes as $route) {
        get($route)->assertRedirect('/login');
    }
});
