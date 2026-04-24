<div>
    <flux:header>
        <flux:heading size="lg">Live Monitor</flux:heading>
        <flux:spacer />
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            <span class="text-sm text-gray-500">Auto-refresh: {{ $refreshInterval }}s</span>
            <flux:badge variant="success">{{ $totalActive }} active</flux:badge>
        </div>
    </flux:header>

    {{-- Auto-refresh via Livewire polling --}}
    <div wire:poll.{{ $refreshInterval }}s="refreshActiveSessions" wire:loading.remove>
        @if(empty($activeSessions))
            <flux:card>
                <flux:callout variant="success">
                    No active exam sessions right now. Students are not taking any exams.
                </flux:callout>
            </flux:card>
        @else
            {{-- Active Sessions List --}}
            <div class="space-y-4">
                @foreach($activeSessions as $session)
                    <flux:card>
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-lg">{{ $session['student_name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $session['student_email'] }}</div>
                                <div class="text-sm mt-1">
                                    <span class="text-gray-500">Exam:</span> {{ $session['exam_title'] }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-mono {{ $session['time_remaining'] === 'EXPIRED' ? 'text-red-600' : 'text-blue-600' }}">
                                    {{ $session['time_remaining'] }}
                                </div>
                                <div class="text-sm text-gray-500">remaining</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Progress</span>
                                    <div class="font-medium">
                                        {{ $session['answers_count'] }} / {{ $session['total_questions'] }}
                                        <span class="text-gray-400">({{ $session['progress_percent'] }}%)</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Started</span>
                                    <div class="font-medium">{{ $session['started_at'] }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Tab Switches</span>
                                    <div class="font-medium {{ $session['tab_switch_count'] > 2 ? 'text-red-600' : '' }}">
                                        {{ $session['tab_switch_count'] }}
                                        @if($session['tab_switch_count'] > 2)
                                            <flux:badge variant="warning" size="sm">HIGH</flux:badge>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Flagged Questions</span>
                                    <div class="font-medium">
                                        @if(count($session['flagged_questions']) > 0)
                                            <flux:badge variant="warning" size="sm">
                                                {{ count($session['flagged_questions']) }}
                                            </flux:badge>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $session['progress_percent'] }}%"></div>
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif

        <div class="text-center text-sm text-gray-400 mt-4">
            Last updated: {{ $lastUpdate }}
        </div>
    </div>

    {{-- Loading state --}}
    <div wire:loading wire:target="refreshActiveSessions" class="text-center py-8">
        <flux:icon name="arrow-path" class="w-8 h-8 text-gray-400 animate-spin mx-auto" />
        <div class="text-gray-500 mt-2">Refreshing...</div>
    </div>
</div>