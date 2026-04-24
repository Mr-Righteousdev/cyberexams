<div>
    <flux:header>
        <flux:heading size="lg">Manual Grading Queue</flux:heading>
        <flux:spacer />
        <flux:badge variant="warning">{{ $stats['total'] }} pending</flux:badge>
    </flux:header>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:card>
            <flux:heading size="sm">Total Pending</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $stats['total'] }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Fully Graded Sessions</flux:heading>
            <flux:text size="xl" class="font-bold text-green-600">
                {{ $stats['total'] > 0 ? '—' : 0 }}
            </flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Needs Review</flux:heading>
            <flux:text size="xl" class="font-bold text-yellow-600">
                {{ $stats['total'] }}
            </flux:text>
        </flux:card>
    </div>

    {{-- Pending Answers Table --}}
    <flux:card>
        @if($pendingAnswers->isEmpty())
            <flux:callout variant="success">All short answers have been graded!</flux:callout>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Exam</flux:table.column>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Question</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Marks</flux:table.column>
                    <flux:table.column>Submitted</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($pendingAnswers as $answer)
                        <flux:table.row>
                            <flux:table.cell>
                                {{ $answer->session->exam->title }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ $answer->session->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $answer->session->user->email }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="max-w-xs truncate">{{ $answer->question->question_text }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge variant="neutral" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $answer->question->type)) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $answer->question->marks }} max
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-gray-500">
                                {{ $answer->session->submitted_at?->format('M j, H:i') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="primary" href="{{ route('admin.manual-grading-answer', $answer->id) }}">
                                    Grade
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>