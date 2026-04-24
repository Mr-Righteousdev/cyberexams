<?php

namespace App\Http\Controllers;

use App\Jobs\ImportQuestionsJob;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamExportController extends Controller
{
    public function export(Exam $exam): StreamedResponse
    {
        $questions = $exam->questions()->with('options')->get();

        $data = [
            'exam_title' => $exam->title,
            'version' => 1,
            'questions' => $questions->map(fn (Question $q) => [
                'type' => $q->type,
                'question_text' => $q->question_text,
                'marks' => $q->marks,
                'code_block' => $q->code_block,
                'language' => $q->code_language,
                'options' => $q->options->map(fn (Option $o) => [
                    'option_text' => $o->option_text,
                    'is_correct' => (bool) $o->is_correct,
                ])->toArray(),
            ])->toArray(),
        ];

        $filename = str_replace(' ', '_', $exam->title).'_questions_'.now()->format('Ymd_His').'.json';

        return response()->streamDownload(
            fn () => file_put_contents('php://output', json_encode($data, JSON_PRETTY_PRINT)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function sampleTemplate(Exam $exam): StreamedResponse
    {
        $data = [
            'exam_title' => $exam->title,
            'version' => 1,
            'questions' => [
                [
                    'type' => 'mcq',
                    'question_text' => 'What is 2 + 2?',
                    'marks' => 5,
                    'code_block' => null,
                    'language' => null,
                    'options' => [
                        ['option_text' => '3', 'is_correct' => false],
                        ['option_text' => '4', 'is_correct' => true],
                        ['option_text' => '5', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'true_false',
                    'question_text' => 'The earth is flat.',
                    'marks' => 2,
                    'code_block' => null,
                    'language' => null,
                    'options' => [
                        ['option_text' => 'True', 'is_correct' => false],
                        ['option_text' => 'False', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'short_answer',
                    'question_text' => 'What is the capital of France?',
                    'marks' => 5,
                    'code_block' => null,
                    'language' => null,
                    'options' => [],
                ],
                [
                    'type' => 'code_snippet',
                    'question_text' => 'Fix the bug in this function',
                    'marks' => 10,
                    'code_block' => "function add(a, b) {\n    return a - b;\n}",
                    'language' => 'javascript',
                    'options' => [],
                ],
            ],
        ];

        $filename = 'sample_questions_template.json';

        return response()->streamDownload(
            fn () => file_put_contents('php://output', json_encode($data, JSON_PRETTY_PRINT)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function import(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'questions_file' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $file = $request->file('questions_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Invalid JSON file: '.json_last_error_msg());
        }

        if (! isset($data['questions']) || ! is_array($data['questions'])) {
            return redirect()->back()->with('error', 'JSON must contain a "questions" array.');
        }

        $questionCount = count($data['questions']);

        if ($questionCount === 0) {
            return redirect()->back()->with('error', 'No questions found in the JSON file.');
        }

        Log::info('Import started', [
            'exam_id' => $exam->id,
            'user_id' => auth()->id(),
            'question_count' => $questionCount,
        ]);

        ImportQuestionsJob::dispatch($exam, $data['questions'], auth()->id());

        return redirect()->back()->with('info', "Import started! You will be notified when it completes ({$questionCount} questions).");
    }
}
