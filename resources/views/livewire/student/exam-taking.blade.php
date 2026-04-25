<div>
    <!-- Anti-cheat guard script -->
    <script>
    function examGuard() {
        return {
            tabCount: {{ $tabSwitchCount }},
            showWarning: {{ $showTabWarning ? 'true' : 'false' }},
            lastBlocked: '',

            init() {
                this.tabCount = {{ $tabSwitchCount }};
                this.showWarning = {{ $showTabWarning ? 'true' : 'false' }};
            },

            handleKeydown(event) {
                if (event.ctrlKey && event.key.toLowerCase() === 'u') {
                    event.preventDefault();
                    this.showBlocked('Ctrl+U is disabled');
                    return false;
                }
                if (event.ctrlKey && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    this.showBlocked('Ctrl+S is disabled');
                    return false;
                }
                if (event.ctrlKey && event.key.toLowerCase() === 'p') {
                    event.preventDefault();
                    this.showBlocked('Ctrl+P is disabled');
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.key.toLowerCase() === 'i') {
                    event.preventDefault();
                    this.showBlocked('Developer tools are disabled');
                    return false;
                }
                if (event.key === 'F12') {
                    event.preventDefault();
                    this.showBlocked('F12 is disabled');
                    return false;
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                }
            },

            handleVisibilityChange() {
                if (document.hidden) {
                    @this.handleTabSwitch();
                }
            },

            showBlocked(message) {
                if (message !== this.lastBlocked) {
                    this.lastBlocked = message;
                    @this.dispatch('blocked-action', {message: message});
                }
            }
        };
    }
    </script>

    <!-- Header -->
    <div class="sticky top-0 z-10 border-b border-zinc-200 bg-white py-3 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="mx-auto max-w-4xl flex items-center justify-between px-4">
            <div class="flex items-center gap-4">
                <flux:heading level="2" size="md">{{ $exam->title }}</flux:heading>
                @if($tabSwitchCount > 0)
                    <span class="rounded bg-amber-100 px-2 py-1 text-xs text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                        {{ $tabSwitchCount }} / {{ $maxTabSwitches }} tabs
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <div x-data="{ time: {{ $remainingSeconds }} }"
                     x-init="setInterval(() => { if (time > 0) time--; if (time <= 0) location.reload(); }, 1000)"
                     class="flex items-center gap-2 rounded-lg bg-zinc-100 px-4 py-2 dark:bg-zinc-700">
                    <flux:icon name="clock" class="h-5 w-5" />
                    <span x-text="Math.floor(time / 3600) + ':' + Math.floor((time % 3600) / 60).toString().padStart(2, '0') + ':' + (time % 60).toString().padStart(2, '0')"
                          class="font-mono text-xl font-bold"
                          :class="{ 'text-red-600': time < 300 }"></span>
                </div>
                <flux:button variant="danger" wire:click="$set('showSubmitModal', true)">
                    Submit Exam
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-4xl px-4 py-6 select-none"
         x-data="examGuard()"
         x-init="init()"
         x-on:keydown.window="handleKeydown($event)"
         x-on:contextmenu.window.prevent="$flux.toast({variant: 'warning', text: 'Right-click is disabled during exam'})"
         x-on:copy.window.prevent="$flux.toast({variant: 'warning', text: 'Copy is disabled during exam'})"
         x-on:paste.window.prevent="$flux.toast({variant: 'warning', text: 'Paste is disabled during exam'})"
         x-on:cut.window.prevent="$flux.toast({variant: 'warning', text: 'Cut is disabled during exam'})"
         x-on:visibilitychange.window="handleVisibilityChange()"
         x-on:blocked-action.window="$wire.handleBlockedAction($event.detail.message)">

        <!-- Question Navigation Pills -->
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $this->getAnsweredCount() }} of {{ $questions->count() }} answered
            </span>
            @foreach($questions as $index => $question)
                @php($isAnswered = $this->isAnswered($question->id))
                @php($isCurrent = $index === $currentIndex)
                @php($isFlagged = $this->isFlagged($question->id))
                <button wire:click="goToQuestion({{ $index }})"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium transition-all
                        {{ $isCurrent ? 'ring-2 ring-indigo-600 ring-offset-2 dark:ring-offset-zinc-800' : '' }}
                        {{ $isAnswered && !$isCurrent ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $isFlagged ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                        {{ !$isAnswered && !$isCurrent && !$isFlagged ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' : '' }}">
                    {{ $index + 1 }}
                </button>
            @endforeach
        </div>

        <!-- Question Card -->
        @php($currentQuestion = $this->getCurrentQuestion())
        @php($currentAnswer = $answers[$currentQuestion->id] ?? ['selected_option_id' => null, 'text_answer' => null])
        <flux:card class="mt-6">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <flux:heading level="3" size="lg">
                        Question {{ $currentIndex + 1 }} of {{ $questions->count() }}
                    </flux:heading>
                    <flux:badge>{{ $currentQuestion->marks }} {{ $currentQuestion->marks == 1 ? 'mark' : 'marks' }}</flux:badge>
                </div>
                <flux:button variant="ghost" size="xs" wire:click="toggleFlag({{ $currentQuestion->id }})">
                    <flux:icon name="flag" :variant="$this->isFlagged($currentQuestion->id) ? 'solid' : 'outline'" class="mr-1 h-4 w-4" />
                    {{ $this->isFlagged($currentQuestion->id) ? 'Flagged' : 'Flag for review' }}
                </flux:button>

                <div class="prose dark:prose-invert max-w-none">
                    <p class="text-lg">{{ $currentQuestion->question_text }}</p>
                    @if($currentQuestion->code_block)
                        <pre class="mt-4 overflow-x-auto rounded-lg bg-zinc-900 p-4"><code class="language-{{ $currentQuestion->code_language }}">{{ $currentQuestion->code_block }}</code></pre>
                    @endif
                </div>

                <div>
                    @switch($currentQuestion->type)
                        @case('mcq')
                            <div class="space-y-3">
                                @foreach($currentQuestion->options as $option)
                                    @php($isChecked = $this->isOptionSelected($currentQuestion->id, $option->id))
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-4 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800 {{ $isChecked ? 'border-indigo-600 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20' : '' }}">
                                        <input type="checkbox"
                                               value="{{ $option->id }}"
                                               {{ $isChecked ? 'checked' : '' }}
                                               wire:click="toggleOption({{ $currentQuestion->id }}, {{ $option->id }})"
                                               class="h-5 w-5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-600">
                                        <span class="flex-1">{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break
                        @case('true_false')
                            @php($tfOptions = $currentQuestion->options->sortBy('order')->values())
                            <div class="flex gap-4">
                                <flux:button variant="{{ in_array($tfOptions[0]->id ?? 0, $currentAnswer['selected_options'] ?? [$currentAnswer['selected_option_id']]) ? 'primary' : 'ghost' }}"
                                           wire:click="saveAnswer({{ $currentQuestion->id }}, {{ $tfOptions[0]->id ?? 0 }})"
                                           class="flex-1 py-4 text-lg">{{ $tfOptions[0]->option_text ?? 'True' }}</flux:button>
                                <flux:button variant="{{ in_array($tfOptions[1]->id ?? 0, $currentAnswer['selected_options'] ?? [$currentAnswer['selected_option_id']]) ? 'primary' : 'ghost' }}"
                                           wire:click="saveAnswer({{ $currentQuestion->id }}, {{ $tfOptions[1]->id ?? 0 }})"
                                           class="flex-1 py-4 text-lg">{{ $tfOptions[1]->option_text ?? 'False' }}</flux:button>
                            </div>
                            @break
                        @case('short_answer')
                            <flux:textarea wire:change="saveAnswer({{ $currentQuestion->id }}, $event.target.value)" rows="4" class="w-full" placeholder="Type your answer here...">{{ $currentAnswer['text_answer'] ?? '' }}</flux:textarea>
                            @break
                        @case('code_snippet')
                            @if($currentQuestion->code_block)
                                <pre class="mt-2 overflow-x-auto rounded-lg bg-zinc-900 p-4"><code class="language-{{ $currentQuestion->code_language }}">{{ $currentQuestion->code_block }}</code></pre>
                            @endif
                            <flux:textarea wire:change="saveAnswer({{ $currentQuestion->id }}, $event.target.value)" rows="6" class="mt-4 w-full" placeholder="Write your code here...">{{ $currentAnswer['text_answer'] ?? '' }}</flux:textarea>
                            @break
                    @endswitch
                </div>

                {{-- <div class="flex items-center justify-between border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="navigate('prev')" {{ $currentIndex === 0 ? 'disabled' : '' }}>
                        <flux:icon name="chevron-left" class="mr-1 h-5 w-5" />Previous
                    </flux:button>
                    <flux:button variant="ghost" wire:click="navigate('next')" {{ $currentIndex === count($questions) - 1 ? 'disabled' : '' }}>
                        Next<flux:icon name="chevron-right" class="ml-1 h-5 w-5" />
                    </flux:button>
                </div> --}}

                <div class="flex items-center justify-between border-t border-zinc-200 pt-6 dark:border-zinc-700">

    <flux:button
        variant="ghost"
        wire:click="navigate('prev')"
        :disabled="$currentIndex === 0"
    >
        <flux:icon name="chevron-left" class="mr-1 h-5 w-5" />
        Previous
    </flux:button>

    <flux:button
        variant="ghost"
        wire:click="navigate('next')"
        :disabled="$currentIndex === count($questions) - 1"
    >
        Next
        <flux:icon name="chevron-right" class="ml-1 h-5 w-5" />
    </flux:button>

</div>
            </div>
        </flux:card>
    </div>

    <!-- Submit Modal -->
    @if($showSubmitModal)
        <div x-data="{ show: @entangle('showSubmitModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:leave="transition ease-in duration-150"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div @click.outside="$set('showSubmitModal', false)" class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-zinc-800">
                <flux:heading level="3" size="lg">Submit Exam?</flux:heading>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                    You have answered {{ $this->getAnsweredCount() }} of {{ $questions->count() }} questions.
                    @if($questions->count() - $this->getAnsweredCount() > 0)
                        <br><strong class="text-amber-600">{{ $questions->count() - $this->getAnsweredCount() }} questions unanswered</strong>
                    @endif
                </p>
                <div class="mt-6 flex gap-3">
                    <flux:button variant="ghost" wire:click="$set('showSubmitModal', false)" class="flex-1">Cancel</flux:button>
                    <flux:button variant="primary" wire:click="submit" class="flex-1">Confirm Submit</flux:button>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab Switch Warning Overlay -->
    @if($showTabWarning)
        <div x-data="{ show: @entangle('showTabWarning') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             class="fixed inset-0 z-50 flex items-center justify-center bg-amber-500/90">
            <div class="text-center text-white">
                <flux:icon name="exclamation-triangle" class="mx-auto h-16 w-16" />
                <flux:heading level="2" size="xl" class="mt-4">Warning: Tab Switch Detected</flux:heading>
                <p class="mt-2 text-lg">Do not leave the exam page during the exam.<br>This is your {{ $tabSwitchCount }} tab switch.</p>
                <p class="mt-4 text-amber-100">After {{ $maxTabSwitches }} tab switches, your exam will be automatically submitted.</p>
                <flux:button variant="primary" wire:click="dismissTabWarning" class="mt-6">I Understand - Continue Exam</flux:button>
            </div>
        </div>
    @endif

    <!-- IP Change Warning -->
    @if($isIpChanged)
        <div class="fixed bottom-4 right-4 z-40 max-w-sm rounded-lg bg-amber-500 p-4 text-white shadow-lg">
            <div class="flex items-start gap-3">
                <flux:icon name="exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
                <p class="text-sm">{{ $ipChangeWarning }}</p>
            </div>
        </div>
    @endif
</div>
