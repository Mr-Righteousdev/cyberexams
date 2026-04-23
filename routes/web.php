<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\ExamCreate;
use App\Livewire\Admin\ExamIndex;
use App\Livewire\Admin\QuestionCreate;
use App\Livewire\Admin\QuestionIndex;
use App\Livewire\Admin\StudentCreate;
use App\Livewire\Admin\StudentIndex;
use App\Livewire\Student\Dashboard;
use App\Livewire\Student\ExamStart;
use App\Livewire\Student\ExamTaking;
use App\Livewire\Student\Results;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/blocked', function () {
    return view('errors.network-blocked');
})->name('network.blocked');

Route::middleware(['auth', 'verified', 'local.network'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/exams', ExamIndex::class)->name('exams.index');
        Route::get('/exams/create', ExamCreate::class)->name('exams.create');
        Route::get('/exams/{exam}/edit', ExamCreate::class)->name('exams.edit');
        Route::get('/exams/{exam}/questions', QuestionIndex::class)->name('exams.questions.index');
        Route::get('/exams/{exam}/questions/create', QuestionCreate::class)->name('exams.questions.create');
        Route::get('/exams/{exam}/questions/{question}/edit', QuestionCreate::class)->name('exams.questions.edit');

        Route::get('/students', StudentIndex::class)->name('students.index');
        Route::get('/students/create', StudentCreate::class)->name('students.create');
        Route::get('/students/{student}/edit', StudentCreate::class)->name('students.edit');

        Route::get('/results', function () {
            return view('admin.results.index');
        })->name('results.index');
    });

    Route::middleware(['role:student', 'student.active'])->prefix('exam')->name('student.')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/{exam}/start', ExamStart::class)->name('exam.start');
        Route::get('/session/{session}', ExamTaking::class)->name('exam.session');
        Route::get('/results/{session}', Results::class)->name('results');
    });
});

require __DIR__.'/settings.php';
