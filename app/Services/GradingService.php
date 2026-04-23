<?php

namespace App\Services;

use App\Models\ExamSession;

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
        if ($exam->passing_marks !== null && $totalMarks > 0) {
            $passingPercentage = ($exam->passing_marks / $totalMarks) * 100;
            $passed = $percentage >= $passingPercentage;
        }

        $session->update([
            'score' => $awardedMarks,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
        ]);
    }
}
