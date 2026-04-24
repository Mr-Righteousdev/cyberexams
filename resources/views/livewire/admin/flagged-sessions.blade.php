<div>
    <flux:header>
        <flux:heading size="lg">Flagged Sessions</flux:heading>
        <flux:spacer />
        <flux:badge variant="warning">{{ $flaggedSessions->total() }} flagged</flux:badge>
    </flux:header>

    <flux:card>
        @if($flaggedSessions->isEmpty())
            <flux:callout variant="success">
                No flagged sessions. All exam sessions passed without violations.
            </flux:callout>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Exam</flux:table.column>
                    <flux:table.column>Score</flux:table.column>
                    <flux:table.column>Flag Reason</flux:table.column>
                    <flux:table.column>Tab Switches</flux:table.column>
                    <flux:table.column>Submitted</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($flaggedSessions as $session)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ $session->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $session->user->email }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $session->exam->title }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $session->percentage >= 60 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $session->percentage ?? 0 }}%
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm">{{ $session->flag_reason }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $session->tab_switch_count ?? 0 }}
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-gray-500">
                                {{ $session->submitted_at?->format('M j, H:i') }}
                            </flux:table.cell>
                            <flux:table.cell class="flex gap-2">
                                <flux:button size="sm" variant="outline" href="{{ route('admin.session-results', $session->id) }}">
                                    View
                                </flux:button>
                                <flux:button size="sm" variant="subtle" wire:click="clearFlag({{ $session->id }})">
                                    Clear Flag
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $flaggedSessions->links() }}
            </div>
        @endif
    </flux:card>
</div>