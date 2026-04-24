<?php

use App\Http\Controllers\ExamExportController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ExamCreate;
use App\Livewire\Admin\ExamIndex;
use App\Livewire\Admin\ExamResults;
use App\Livewire\Admin\ExamResultsExport;
use App\Livewire\Admin\ExamStats;
use App\Livewire\Admin\FlaggedSessions;
use App\Livewire\Admin\GradingQueue;
use App\Livewire\Admin\LiveMonitor;
use App\Livewire\Admin\ManualGrading;
use App\Livewire\Admin\Notifications;
use App\Livewire\Admin\QuestionCreate;
use App\Livewire\Admin\QuestionIndex;
use App\Livewire\Admin\SessionResults;
use App\Livewire\Admin\StudentCreate;
use App\Livewire\Admin\StudentEdit;
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

Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'verified', 'local.network'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

        Route::get('/exams', ExamIndex::class)->name('exams.index');
        Route::get('/exams/create', ExamCreate::class)->name('exams.create');
        Route::get('/exams/{exam}/edit', ExamCreate::class)->name('exams.edit');
        Route::get('/exams/{exam}/questions', QuestionIndex::class)->name('exams.questions.index');
        Route::get('/exams/{exam}/questions/create', QuestionCreate::class)->name('exams.questions.create');
        Route::get('/exams/{exam}/questions/{question}/edit', QuestionCreate::class)->name('exams.questions.edit');
        Route::get('/exams/{exam}/stats', ExamStats::class)->name('exams.stats');
        Route::get('/exams/{exam}/export', [ExamExportController::class, 'export'])->name('exams.export');
        Route::get('/exams/{exam}/sample-template', [ExamExportController::class, 'sampleTemplate'])->name('exams.sample-template');
        Route::post('/exams/{exam}/import', [ExamExportController::class, 'import'])->name('exams.import');
        Route::get('/exams/{exam}/results', ExamResults::class)->name('exam-results');
        Route::get('/exams/{exam}/results/export', ExamResultsExport::class)->name('exam-results.export');
        Route::get('/sessions/{session}/results', SessionResults::class)->name('session-results');

        Route::get('/grading/queue', GradingQueue::class)->name('grading-queue');
        Route::get('/grading/answer/{answer}', ManualGrading::class)->name('manual-grading-answer');
        Route::get('/monitor', LiveMonitor::class)->name('monitor');
        Route::get('/flagged', FlaggedSessions::class)->name('flagged-sessions');
        Route::get('/notifications', Notifications::class)->name('notifications');

        Route::get('/students', StudentIndex::class)->name('students.index');
        Route::get('/students/create', StudentCreate::class)->name('students.create');
        Route::get('/students/{student}/edit', StudentEdit::class)->name('students.edit');

    });

    Route::middleware(['role:student', 'student.active'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/{exam}/start', ExamStart::class)->name('exam.start');
        Route::get('/session/{session}', ExamTaking::class)->name('exam.session');
        Route::get('/results/{session}', Results::class)->name('results');
    });
});

require __DIR__.'/settings.php';
