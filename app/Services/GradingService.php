<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ExamSession;
use Illuminate\Database\Eloquent\Collection;

class GradingService
{
    public static function grade(ExamSession $session): void
    {
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

            if (in_array($question->type, ['mcq', 'true_false'])) {
                $selectedIds = $answer->selected_options
                    ?? ($answer->selected_option_id ? [$answer->selected_option_id] : []);

                $selectedIds = array_filter($selectedIds);

                if (empty($selectedIds)) {
                    $answer->update([
                        'is_correct' => false,
                        'marks_awarded' => 0,
                    ]);
                } elseif ($question->type === 'true_false') {
                    $selectedId = reset($selectedIds);
                    $selectedOption = $question->options()->find($selectedId);
                    $selectedText = $selectedOption?->option_text ?? '';

                    $correctOption = $question->options()->where('is_correct', true)->first();
                    $correctText = $correctOption?->option_text ?? '';

                    $isCorrect = strtolower(trim($selectedText)) === strtolower(trim($correctText));

                    $answer->update([
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $isCorrect ? $question->marks : 0,
                    ]);

                    if ($isCorrect) {
                        $awardedMarks += $question->marks;
                    }
                } else {
                    $correctOptionIds = $question->options()
                        ->where('is_correct', true)
                        ->pluck('id')
                        ->toArray();

                    sort($correctOptionIds);
                    sort($selectedIds);

                    $isCorrect = ! empty($correctOptionIds) && $correctOptionIds === $selectedIds;

                    $answer->update([
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $isCorrect ? $question->marks : 0,
                    ]);

                    if ($isCorrect) {
                        $awardedMarks += $question->marks;
                    }
                }
            } elseif (in_array($question->type, ['short_answer', 'code_snippet'])) {
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
            'total_received' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
        ]);
    }

    public static function gradeAnswer(Answer $answer, float|string $marks): void
    {
        $marks = is_numeric($marks) ? (float) $marks : 0;

        $question = $answer->question;
        $marks = min($marks, $question->marks);
        $marks = max($marks, 0);

        $answer->update([
            'marks_awarded' => $marks,
            'is_correct' => $marks >= $question->marks ? true : ($marks > 0 ? null : false),
        ]);

        self::recalculateSession($answer->session);
    }

    public static function recalculateSession(ExamSession $session): void
    {
        $answers = $session->answers()->with('question')->get();

        $totalMarks = 0;
        $awardedMarks = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $totalMarks += $question->marks;

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

        $updateData = [
            'score' => $awardedMarks,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
        ];

        if ($session->total_received === null) {
            $updateData['total_received'] = $totalMarks;
        }

        $session->update($updateData);
    }

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
