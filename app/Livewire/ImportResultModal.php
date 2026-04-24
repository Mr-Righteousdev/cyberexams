<?php

namespace App\Livewire;

use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportResultModal extends Component
{
    public bool $show = false;

    public array $results = [];

    public string $examTitle = '';

    public int $examId = 0;

    protected string $eventName = 'show-import-result-modal';

    #[Listen('show-import-result-modal')]
    public function open(array $data): void
    {
        $this->results = $data;
        $this->examTitle = $data['exam_title'] ?? '';
        $this->examId = $data['exam_id'] ?? 0;
        $this->show = true;
    }

    public function downloadFailures(): StreamedResponse
    {
        $failures = array_map(fn ($f) => [
            'question_text' => $f['question_text'],
            'type' => $f['type'],
            'error' => $f['error'],
        ], $this->results['failures'] ?? []);

        $filename = 'failed_questions_'.now()->format('Ymd_His').'.json';

        return response()->streamDownload(
            fn () => file_put_contents('php://output', json_encode(['failed_questions' => $failures], JSON_PRETTY_PRINT)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function render()
    {
        return view('livewire.import-result-modal');
    }
}
