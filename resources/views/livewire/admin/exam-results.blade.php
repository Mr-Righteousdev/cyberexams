<div>
    <flux:header>
        <flux:heading size="lg">{{ $exam->title }} — Results</flux:heading>
        <flux:spacer />
        <flux:button variant="primary" href="{{ route('admin.exam-results.export', $exam->id) }}">
            <flux:icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
            Export CSV
        </flux:button>
    </flux:header>

    {{-- Analytics Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:card>
            <flux:heading size="sm">Total Attempts</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $analytics['total_students'] }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Average Score</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $analytics['avg_score'] }}%</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Pass Rate</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $analytics['pass_rate'] }}%</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Total Marks</flux:heading>
            <flux:text size="xl" class="font-bold">{{ $analytics['total_marks'] }}</flux:text>
        </flux:card>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input
            wire:model.live="search"
            placeholder="Search by name, email, or student number..."
            icon="magnifying-glass"
        />
    </div>

    {{-- Results Table --}}
    <flux:card>
        @if($sessions->isEmpty())
            <x-empty-state
                title="No submissions yet"
                description="Students haven't submitted this exam yet."
                icon="clipboard"
            />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Score</flux:table.column>
                    <flux:table.column>Percentage</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Time (min)</flux:table.column>
                    <flux:table.column>Flagged</flux:table.column>
                    <flux:table.column>Submitted</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($sessions as $session)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ $session->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $session->user->email }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $session->score ?? 0 }} / {{ $session->total_marks ?? 0 }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $session->percentage >= 60 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $session->percentage ?? 0 }}%
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($session->passed === true)
                                    <flux:badge variant="success">PASS</flux:badge>
                                @elseif($session->passed === false)
                                    <flux:badge variant="danger">FAIL</flux:badge>
                                @else
                                    <flux:badge variant="neutral">PENDING</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($session->started_at && $session->submitted_at)
                                    {{ $session->started_at->diffInMinutes($session->submitted_at) }}
                                @else
                                    N/A
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($session->is_flagged)
                                    <flux:badge variant="warning">FLAGGED</flux:badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-gray-500">
                                {{ $session->submitted_at?->format('M j, H:i') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="outline" href="{{ route('admin.session-results', $session->id) }}">
                                    View
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $sessions->links() }}
            </div>
        @endif
    </flux:card>
</div>