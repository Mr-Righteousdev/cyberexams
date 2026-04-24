<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Services\AnalyticsService;
use Livewire\Component;
use Livewire\WithPagination;

class ExamResults extends Component
{
    use WithPagination;

    public Exam $exam;

    public string $search = '';

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
    }

    public function getSessionsProperty()
    {
        $query = $this->exam->sessions()
            ->where('is_submitted', true)
            ->with('user');

        if ($this->search) {
            $query->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('student_number', 'like', "%{$this->search}%")
            );
        }

        return $query->latest('submitted_at')->paginate(20);
    }

    public function getAnalyticsProperty(): array
    {
        return AnalyticsService::getExamAnalytics($this->exam);
    }

    public function render()
    {
        return view('livewire.admin.exam-results', [
            'sessions' => $this->sessions,
            'analytics' => $this->analytics,
        ]);
    }
}
