<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Services\AnalyticsService;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamResultsExport extends Component
{
    public int $examId;

    public ?Exam $exam = null;

    public function mount(int $examId): void
    {
        $this->examId = $examId;
        $this->exam = Exam::findOrFail($examId);
    }

    public function exportCsv(): StreamedResponse
    {
        $csv = AnalyticsService::exportExamCsv($this->exam);

        return response()->streamDownload(
            fn () => print ($csv),
            'exam-results-'.\Str::slug($this->exam->title).'-'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    public function render()
    {
        return view('livewire.admin.exam-results-export');
    }
}
