 <div class="mx-auto max-w-4xl space-y-8">
        <!-- Score Card -->
        <flux:card>
            <div class="flex flex-col items-center gap-6 text-center">
                <flux:heading level="1" size="2xl">{{ $session->exam->title }}</flux:heading>
                <p class="text-zinc-500 dark:text-zinc-400">Completed on {{ $session->submitted_at?->format('M d, Y at H:i') }}</p>

                <div class="flex items-center gap-8">
                    <div>
                        <p class="text-5xl font-bold text-zinc-900 dark:text-white">{{ $session->score }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">of {{ $session->total_marks }} marks</p>
                    </div>
                    <div class="h-16 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                    <div>
                        <p class="text-5xl font-bold text-zinc-900 dark:text-white">{{ $session->percentage }}%</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Score</p>
                    </div>
                    <div class="h-16 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                    <div>
                        @if($session->passed === null)
                            <flux:badge variant="neutral" class="text-lg px-4 py-2">No Passing Threshold</flux:badge>
                        @elseif($session->passed)
                            <flux:badge variant="success" class="text-lg px-4 py-2">PASSED</flux:badge>
                        @else
                            <flux:badge variant="danger" class="text-lg px-4 py-2">FAILED</flux:badge>
                        @endif
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            @if($session->exam->passing_percentage)
                                Passing: {{ $session->exam->passing_percentage }}%
                            @else
                                Passing: 50% (default)
                            @endif
                        </p>
                    </div>
                </div>

                <flux:button variant="primary" href="{{ route('student.dashboard') }}">
                    Return to Dashboard
                </flux:button>
            </div>
        </flux:card>

        <!-- Question Breakdown -->
        <section>
            <flux:heading level="2" size="lg">Question Breakdown</flux:heading>

            <div class="mt-4 space-y-4">
                @foreach($session->answers as $index => $answer)
                    @php($question = $answer->question)
                    <flux:card>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:heading level="3" size="sm">Question {{ $index + 1 }}</flux:heading>
                                    <flux:badge>
                                        {{ $question->type }}
                                    </flux:badge>
                                </div>
                                <flux:badge :variant="$answer->is_correct === true ? 'success' : ($answer->is_correct === false ? 'danger' : 'warning')">
                                    @if($answer->is_correct === true)
                                        Correct
                                    @elseif($answer->is_correct === false)
                                        Incorrect
                                    @else
                                        Needs Review
                                    @endif
                                </flux:badge>
                            </div>

                            <p class="text-zinc-900 dark:text-zinc-100">{{ $question->question_text }}</p>

                            @if($question->code_block)
                                <pre class="overflow-x-auto rounded-lg bg-zinc-900 p-4 text-sm text-zinc-100"><code>{{ $question->code_block }}</code></pre>
                            @endif

                            <!-- Student's Answer -->
                            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                                <p class="mb-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">Your Answer:</p>
                                @if(in_array($question->type, ['mcq', 'true_false']))
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ $answer->selectedOption?->option_text ?? 'Not answered' }}
                                    </p>
                                @else
                                    <p class="whitespace-pre-wrap text-zinc-900 dark:text-white">{{ $answer->text_answer ?? 'Not answered' }}</p>
                                @endif
                            </div>

                            <!-- Correct Answer (for MCQ/TF) -->
                            @if(in_array($question->type, ['mcq', 'true_false']) && !$answer->is_correct)
                                @php($correctOption = $question->options()->where('is_correct', true)->first())
                                @if($correctOption)
                                    <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                        <p class="mb-2 text-sm font-medium text-green-700 dark:text-green-400">Correct Answer:</p>
                                        <p class="font-medium text-green-800 dark:text-green-300">{{ $correctOption->option_text }}</p>
                                    </div>
                                @endif
                            @endif

                            <!-- Marks -->
                            <div class="flex items-center justify-between border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400">
                                    {{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }} possible
                                </span>
                                @if($answer->marks_awarded !== null)
                                    <span class="font-medium {{ $answer->marks_awarded > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $answer->marks_awarded }} marks awarded
                                    </span>
                                @else
                                    <span class="font-medium text-amber-600 dark:text-amber-400">Pending review</span>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </section>

        <!-- Flag Warning -->
        @if($session->is_flagged)
            <flux:callout variant="warning">
                <flux:callout.heading>Exam Flagged</flux:callout.heading>
                <p class="mt-2">Reason: {{ $session->flag_reason }}</p>
            </flux:callout>
        @endif
    </div>
