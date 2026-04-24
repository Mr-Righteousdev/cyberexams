<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Str;

class AnalyticsService
{
    /**
     * Get dashboard stats for all exams.
     * Returns array: exam_count, student_count, total_sessions, avg_score
     */
    public static function getDashboardStats(): array
    {
        $examCount = Exam::where('is_published', true)->count();
        $studentCount = User::where('role', 'student')->where('is_active', true)->count();
        $totalSessions = ExamSession::where('is_submitted', true)->count();

        $avgScore = ExamSession::where('is_submitted', true)
            ->whereNotNull('score')
            ->avg('percentage') ?? 0;

        return [
            'exam_count' => $examCount,
            'student_count' => $studentCount,
            'total_sessions' => $totalSessions,
            'avg_score' => round($avgScore, 2),
            'avg_score_rounded' => round($avgScore, 1),
        ];
    }

    /**
     * Get analytics for a specific exam.
     * Returns: avg_score, pass_rate, total_students, question_difficulty[]
     */
    public static function getExamAnalytics(Exam $exam): array
    {
        $sessions = $exam->sessions()->where('is_submitted', true)->with('answers.question')->get();

        if ($sessions->isEmpty()) {
            return [
                'avg_score' => 0,
                'pass_rate' => 0,
                'total_students' => 0,
                'total_marks' => 0,
                'question_difficulty' => [],
            ];
        }

        $totalStudents = $sessions->count();
        $avgScore = $sessions->avg('percentage') ?? 0;
        $passedCount = $sessions->where('passed', true)->count();
        $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 1) : 0;

        // Question difficulty: % correct per question
        $questions = $exam->questions()->with('options')->get();
        $questionDifficulty = [];

        foreach ($questions as $question) {
            $correctAnswers = 0;
            $totalAnswers = 0;

            foreach ($sessions as $session) {
                $answer = $session->answers->where('question_id', $question->id)->first();
                if ($answer && $answer->is_correct !== null) {
                    $totalAnswers++;
                    if ($answer->is_correct) {
                        $correctAnswers++;
                    }
                }
            }

            $difficultyPercent = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;
            $questionDifficulty[] = [
                'question_id' => $question->id,
                'question_text' => Str::limit($question->question_text, 60),
                'type' => $question->type,
                'total_answers' => $totalAnswers,
                'correct_percent' => $difficultyPercent,
                'difficulty_label' => $difficultyPercent >= 70 ? 'Easy' : ($difficultyPercent >= 40 ? 'Medium' : 'Hard'),
            ];
        }

        return [
            'avg_score' => round($avgScore, 1),
            'pass_rate' => $passRate,
            'total_students' => $totalStudents,
            'total_marks' => $questions->sum('marks'),
            'question_difficulty' => $questionDifficulty,
        ];
    }

    /**
     * Export exam results as CSV.
     * Headers: Student Name, Student Number, Email, Score, Percentage, Pass/Fail, Time Taken, Flagged, Flag Reason
     */
    public static function exportExamCsv(Exam $exam): string
    {
        $sessions = $exam->sessions()
            ->where('is_submitted', true)
            ->with('user')
            ->get()
            ->sortBy(fn ($s) => $s->user->name);

        $lines = [];
        $lines[] = ['Student Name', 'Student Number', 'Email', 'Score', 'Total Marks', 'Percentage %', 'Pass/Fail', 'Time Taken (min)', 'Flagged', 'Flag Reason'];

        foreach ($sessions as $session) {
            $user = $session->user;
            $timeTaken = $session->started_at && $session->submitted_at
                ? round($session->started_at->diffInMinutes($session->submitted_at), 1)
                : 'N/A';

            $lines[] = [
                $user->name,
                $user->student_number ?? 'N/A',
                $user->email,
                $session->score ?? 0,
                $session->total_marks ?? 0,
                $session->percentage ?? 0,
                $session->passed === true ? 'PASS' : ($session->passed === false ? 'FAIL' : 'N/A'),
                $timeTaken,
                $session->is_flagged ? 'YES' : 'NO',
                $session->flag_reason ?? '',
            ];
        }

        $csv = '';
        foreach ($lines as $line) {
            $csv .= '"'.implode('","', array_map(fn ($v) => str_replace('"', '""', (string) $v), $line)).'"'."\n";
        }

        return $csv;
    }
}
