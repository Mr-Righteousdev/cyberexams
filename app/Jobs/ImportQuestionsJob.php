<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use App\Notifications\ImportQuestionsComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public Exam $exam,
        public array $questions,
        public int $userId
    ) {}

    public function handle(): void
    {
        $results = [
            'total' => count($this->questions),
            'imported' => 0,
            'skipped_duplicate' => 0,
            'skipped_invalid' => 0,
            'by_type' => [],
            'failures' => [],
        ];

        $existingTexts = $this->exam->questions()
            ->pluck('question_text')
            ->map(fn ($t) => trim(strtolower($t)))
            ->flip()
            ->toArray();

        $maxOrder = $this->exam->questions()->max('order') ?? 0;

        foreach ($this->questions as $index => $questionData) {
            $error = null;

            try {
                $normalizedText = trim(strtolower($questionData['question_text'] ?? ''));

                if (! $normalizedText) {
                    $error = 'Question text is empty.';
                    $results['skipped_invalid']++;
                } elseif (isset($existingTexts[$normalizedText])) {
                    $error = 'Duplicate question text.';
                    $results['skipped_duplicate']++;
                } elseif (! in_array($questionData['type'] ?? '', ['mcq', 'true_false', 'short_answer', 'code_snippet'], true)) {
                    $error = "Unknown type '{$questionData['type']}'.";
                    $results['skipped_invalid']++;
                } else {
                    $isMcq = in_array($questionData['type'], ['mcq', 'true_false']);
                    $hasOptions = ! empty($questionData['options']) && is_array($questionData['options']);

                    if ($isMcq && ! $hasOptions) {
                        $error = 'MCQ/True-False must have at least one option.';
                        $results['skipped_invalid']++;
                    } else {
                        $hasCorrect = $isMcq && collect($questionData['options'])->contains('is_correct', true);
                        if ($isMcq && ! $hasCorrect) {
                            $error = 'MCQ/True-False must have at least one correct option.';
                            $results['skipped_invalid']++;
                        } else {
                            DB::transaction(function () use ($questionData, &$maxOrder, &$results) {
                                $maxOrder++;
                                $question = Question::create([
                                    'exam_id' => $this->exam->id,
                                    'type' => $questionData['type'],
                                    'question_text' => $questionData['question_text'],
                                    'marks' => $questionData['marks'] ?? 1,
                                    'code_block' => $questionData['code_block'] ?? null,
                                    'code_language' => $questionData['language'] ?? null,
                                    'order' => $maxOrder,
                                ]);

                                $type = $questionData['type'];
                                $results['by_type'][$type] = ($results['by_type'][$type] ?? 0) + 1;

                                if (! empty($questionData['options']) && is_array($questionData['options'])) {
                                    $optionOrder = 0;
                                    foreach ($questionData['options'] as $optionData) {
                                        Option::create([
                                            'question_id' => $question->id,
                                            'option_text' => $optionData['option_text'],
                                            'is_correct' => $optionData['is_correct'] ?? false,
                                            'order' => $optionOrder++,
                                        ]);
                                    }
                                }
                            });

                            $existingTexts[$normalizedText] = true;
                            $results['imported']++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $results['skipped_invalid']++;
            }

            if ($error) {
                $results['failures'][] = [
                    'index' => $index + 1,
                    'question_text' => $questionData['question_text'] ?? '(empty)',
                    'type' => $questionData['type'] ?? 'unknown',
                    'error' => $error,
                ];
            }
        }

        Log::info('Import completed', [
            'exam_id' => $this->exam->id,
            'user_id' => $this->userId,
            'results' => $results,
        ]);

        $user = User::find($this->userId);
        $user?->notify(new ImportQuestionsComplete($this->exam, $results));
    }
}
