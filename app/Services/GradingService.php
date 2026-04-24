<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

class GradingService
{
    public static function grade(ExamSession $session): void
    {
        // Already graded
        if ($session->score !== null) {
            return;
        }

        $exam = $session->exam;
        $answers = $session->answers()->with(['question', 'selectedOption'])->get();
        $totalMarks = 0;
        $awardedMarks = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $totalMarks += $question->marks;

            $correctOption = $question->options()->where('is_correct', true)->first();

            if ($question->type === 'mcq' || $question->type === 'true_false') {
                $isCorrect = $answer->selected_option_id !== null
                    && $correctOption !== null
                    && $answer->selected_option_id === $correctOption->id;

                $answer->update([
                    'is_correct' => $isCorrect,
                    'marks_awarded' => $isCorrect ? $question->marks : 0,
                ]);

                if ($isCorrect) {
                    $awardedMarks += $question->marks;
                }
            } elseif ($question->type === 'short_answer' || $question->type === 'code_snippet') {
                // Leave null — manual grading required
            }
        }

        $percentage = $totalMarks > 0 ? round(($awardedMarks / $totalMarks) * 100, 2) : 0;

        $passed = null;
        if ($exam->passing_percentage !== null) {
            $passed = $percentage >= $exam->passing_percentage;
        }

        $session->update([
            'score' => $awardedMarks,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
        ]);
    }

    /**
     * Grade a single manual (short answer / code snippet) question.
     * Updates the answer's marks_awarded and recalculates session totals.
     */
    public static function gradeAnswer(Answer $answer, float|string $marks): void
    {
        $marks = is_numeric($marks) ? (float) $marks : 0;

        // Validate: marks cannot exceed question marks
        $question = $answer->question;
        $marks = min($marks, $question->marks);
        $marks = max($marks, 0); // cannot be negative

        $answer->update([
            'marks_awarded' => $marks,
            'is_correct' => $marks >= $question->marks ? true : ($marks > 0 ? null : false),
        ]);

        // Recalculate session totals
        self::recalculateSession($answer->session);
    }

    /**
     * Recalculate a session's total score after any answer change.
     */
    public static function recalculateSession(ExamSession $session): void
    {
        $answers = $session->answers()->with('question')->get();

        $totalMarks = 0;
        $awardedMarks = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $totalMarks += $question->marks;

            // Only count if marks_awarded is not null (graded questions)
            if ($answer->marks_awarded !== null) {
                $awardedMarks += $answer->marks_awarded;
            }
        }

        $percentage = $totalMarks > 0 ? round(($awardedMarks / $totalMarks) * 100, 2) : 0;

        $passed = null;
        $exam = $session->exam;
        if ($exam->passing_percentage !== null) {
            $passed = $percentage >= $exam->passing_percentage;
        }

        $session->update([
            'score' => $awardedMarks,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
        ]);
    }

    /**
     * Get all answers requiring manual grading.
     * Returns: short_answer and code_snippet questions with null marks_awarded.
     */
    public static function getPendingGrading(): Collection
    {
        return Answer::whereNull('marks_awarded')
            ->whereHas('question', fn ($q) => $q
                ->whereIn('type', ['short_answer', 'code_snippet'])
            )
            ->whereHas('session', fn ($q) => $q->where('is_submitted', true))
            ->with(['session.exam', 'session.user', 'question'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Check if all answers for a session are graded.
     */
    public static function isFullyGraded(ExamSession $session): bool
    {
        $pending = $session->answers()
            ->whereHas('question', fn ($q) => $q
                ->whereIn('type', ['short_answer', 'code_snippet'])
            )
            ->whereNull('marks_awarded')
            ->count();

        return $pending === 0;
    }
}
