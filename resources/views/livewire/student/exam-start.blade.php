<div class="mx-auto max-w-2xl">
        <flux:card>
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading level="1" size="2xl">{{ $exam->title }}</flux:heading>
                    @if($exam->description)
                        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $exam->description }}</p>
                    @endif
                </div>

                @if($exam->starts_at && $exam->starts_at->isFuture() && $exam->starts_at->isAfter(now()->addHours(2)))
                    <flux:callout variant="warning" class="mt-4">
                        This exam will be available at <strong>{{ $exam->starts_at->format('M d, Y H:i') }}</strong>.
                        You will be able to start it 2 hours before that time.
                    </flux:callout>
                @elseif($exam->starts_at && $exam->starts_at->isFuture())
                    <flux:callout variant="info" class="mt-4">
                        This exam is scheduled for <strong>{{ $exam->starts_at->format('M d, Y H:i') }}</strong>.
                        Once you start, you have {{ $exam->duration_minutes }} minutes to complete it.
                    </flux:callout>
                @endif

                <flux:separator />

                <!-- Exam Info -->
                <div class="grid grid-cols-2 gap-4 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-500">Duration</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $exam->duration_minutes }} minutes</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-500">Questions</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $exam->questions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-500">Total Marks</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $exam->total_marks }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-500">Passing %</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $exam->passing_percentage ?? 50 }}%</p>
                    </div>
                </div>

                <!-- Instructions -->
                @if($exam->instructions)
                    <div>
                        <flux:heading level="3" size="md">Instructions</flux:heading>
                        <div class="mt-3 rounded-lg bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                            {!! nl2br(e($exam->instructions)) !!}
                        </div>
                    </div>
                @endif

                <flux:separator />

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <flux:button variant="primary" wire:click="start" class="w-full py-3 text-lg">
                        Start Exam
                    </flux:button>
                    <flux:button variant="ghost" href="{{ route('student.dashboard') }}" class="w-full">
                        Go Back
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>

