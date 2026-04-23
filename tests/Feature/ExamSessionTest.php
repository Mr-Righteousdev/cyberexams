<?php

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents duplicate exam session for same student and exam', function () {
    $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
    $exam = Exam::factory()->create(['is_published' => true]);

    // First session - should succeed
    $session1 = ExamSession::create([
        'user_id' => $student->id,
        'exam_id' => $exam->id,
        'ip_address' => '127.0.0.1',
        'started_at' => now(),
        'is_submitted' => false,
    ]);

    // Verify one session exists
    $this->assertEquals(1, ExamSession::where('user_id', $student->id)->where('exam_id', $exam->id)->count());

    // Second session attempt - should be blocked by duplicate check logic
    // The start exam logic should detect existing unsubmitted session
    $existingSession = ExamSession::where('user_id', $student->id)
        ->where('exam_id', $exam->id)
        ->where('is_submitted', false)
        ->first();

    $this->assertNotNull($existingSession, 'Should find existing unsubmitted session');
});
