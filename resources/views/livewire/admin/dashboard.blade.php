<div>
    <flux:header>
        <flux:heading size="lg">Admin Dashboard</flux:heading>
    </flux:header>

    {{-- Stats Cards Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <flux:card>
            <flux:heading size="sm">Published Exams</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $stats['exam_count'] }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Active Students</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $stats['student_count'] }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Total Exam Attempts</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $stats['total_sessions'] }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Average Score</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $stats['avg_score_rounded'] }}%</flux:text>
        </flux:card>
    </div>

    {{-- Exams Table --}}
    <flux:card>
        <flux:heading size="md" class="mb-4">Exam Overview</flux:heading>

        @if(empty($exams))
            <flux:callout variant="warning">No published exams yet. Create and publish an exam to see analytics.</flux:callout>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Exam</flux:table.column>
                    <flux:table.column>Questions</flux:table.column>
                    <flux:table.column>Attempts</flux:table.column>
                    <flux:table.column>Avg Score</flux:table.column>
                    <flux:table.column>Pass Rate</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($exams as $exam)
                        <flux:table.row>
                            <flux:table.cell>
                                <a href="{{ route('admin.exams.questions.index', $exam['id']) }}" class="text-primary-600 hover:underline">
                                    {{ $exam['title'] }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $exam['question_count'] }}</flux:table.cell>
                            <flux:table.cell>{{ $exam['session_count'] }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $exam['avg_score'] >= 60 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ round($exam['avg_score'], 1) }}%
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $exam['pass_rate'] >= 60 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $exam['pass_rate'] }}%
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="flex gap-2">
                                <flux:button size="sm" variant="outline" href="{{ route('admin.exam-results', $exam['id']) }}">
                                    View Results
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>