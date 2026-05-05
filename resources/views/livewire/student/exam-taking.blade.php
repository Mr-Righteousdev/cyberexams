<div wire:poll.5s="checkSubmissionStatus">

    {{-- Anti-cheat guard script --}}
    <script>
    function examGuard() {
        return {
            init() {
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        @this.handleTabSwitch();
                    }
                });
            },

            handleKeydown(event) {
                const blocked = [
                    event.ctrlKey && event.key.toLowerCase() === 'u',
                    event.ctrlKey && event.key.toLowerCase() === 's',
                    event.ctrlKey && event.key.toLowerCase() === 'p',
                    event.ctrlKey && event.shiftKey && event.key.toLowerCase() === 'i',
                    event.key === 'F12',
                ];

                if (blocked.some(Boolean)) {
                    event.preventDefault();
                    const label = event.key === 'F12' ? 'F12' :
                                  event.ctrlKey && event.shiftKey ? 'Developer tools' :
                                  'Ctrl+' + event.key.toUpperCase();
                    this.reportBlocked(label + ' is disabled');
                    return false;
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                }
            },

            reportBlocked(message) {
                @this.handleBlockedAction(message);
            }
        };
    }
    </script>

    {{-- ============================================================
         STICKY HEADER
    ============================================================ --}}
    <div class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 backdrop-blur-sm dark:bg-zinc-900/95 dark:border-zinc-700">
        <div class="mx-auto max-w-3xl px-4 py-3 flex items-center justify-between gap-4">

            {{-- Left: exam title + tab warning badge --}}
            <div class="flex items-center gap-3 min-w-0">
                <flux:heading level="2" size="sm" class="truncate">{{ $exam->title }}</flux:heading>
                @if($tabSwitchCount > 0)
                    <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        {{ $tabSwitchCount }}/{{ $maxTabSwitches }} tab {{ Str::plural('switch', $tabSwitchCount) }}
                    </span>
                @endif
            </div>

            {{-- Right: timer + submit --}}
            <div class="flex items-center gap-3 shrink-0">
                {{-- Countdown timer — purely Alpine, no Livewire round-trips --}}
                <div x-data="{ time: {{ $remainingSeconds }} }"
                     x-init="
                        let t = setInterval(() => {
                            if (time > 0) { time--; } else { clearInterval(t); location.reload(); }
                        }, 1000);
                     "
                     class="flex items-center gap-1.5 rounded-lg bg-zinc-100 px-3 py-1.5 dark:bg-zinc-800">
                    <flux:icon name="clock" class="h-4 w-4 text-zinc-500 dark:text-zinc-400" />
                    <span x-text="Math.floor(time/3600)+':'+String(Math.floor((time%3600)/60)).padStart(2,'0')+':'+String(time%60).padStart(2,'0')"
                          class="font-mono text-sm font-bold tabular-nums"
                          :class="time < 300 ? 'text-red-600 dark:text-red-400' : 'text-zinc-800 dark:text-zinc-100'">
                    </span>
                </div>

                <flux:button variant="danger" size="sm" wire:click="$set('showSubmitModal', true)">
                    Submit Exam
                </flux:button>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="h-1 bg-zinc-100 dark:bg-zinc-800">
            <div class="h-full bg-indigo-500 transition-all duration-500"
                 style="width: {{ $totalCount > 0 ? round(($answeredCount / $totalCount) * 100) : 0 }}%">
            </div>
        </div>
    </div>

    {{-- ============================================================
         MAIN SCROLL AREA
    ============================================================ --}}
    <div class="mx-auto max-w-3xl px-4 py-8 select-none"
         x-data="examGuard()"
         x-init="init()"
         x-on:keydown.window="handleKeydown($event)"
         x-on:contextmenu.window.prevent="$flux.toast({ variant: 'warning', text: 'Right-click is disabled during exam' })"
         x-on:copy.window.prevent="$flux.toast({ variant: 'warning', text: 'Copy is disabled during exam' })"
         x-on:paste.window.prevent="$flux.toast({ variant: 'warning', text: 'Paste is disabled during exam' })"
         x-on:cut.window.prevent="$flux.toast({ variant: 'warning', text: 'Cut is disabled during exam' })">

        {{-- Summary row --}}
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $answeredCount }}</span>
                of
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $totalCount }}</span>
                questions answered
            </p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Scroll to answer all questions</p>
        </div>

        {{-- ── Question list ── --}}
        @foreach($questions as $index => $question)
            @php
                $qKey      = (string) $question->id;
                $answered  = $this->isAnswered($question->id);
                $flagged   = $this->isFlagged($question->id);
                $qAnswer   = $answers[$qKey] ?? ['selected_option_id' => null, 'selected_options' => [], 'text_answer' => null];
            @endphp

            <div id="question-{{ $question->id }}"
                 wire:key="q-{{ $question->id }}"
                 class="mb-6 rounded-xl border transition-colors
                    {{ $answered  ? 'border-green-300 dark:border-green-700'  : 'border-zinc-200 dark:border-zinc-700' }}
                    {{ $flagged   ? 'border-amber-400 dark:border-amber-500'  : '' }}
                    bg-white dark:bg-zinc-900 shadow-sm">

                {{-- Question header --}}
                <div class="flex items-start justify-between gap-4 px-6 pt-5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        {{-- Number bubble --}}
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $answered ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            {{ $index + 1 }}
                        </span>
                        <flux:heading level="3" size="sm" class="leading-snug">
                            {{ $question->question_text }}
                        </flux:heading>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <flux:badge size="sm">{{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}</flux:badge>
                        <button wire:click="toggleFlag({{ $question->id }})"
                                title="{{ $flagged ? 'Remove flag' : 'Flag for review' }}"
                                class="rounded p-1 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800
                                    {{ $flagged ? 'text-amber-500' : 'text-zinc-300 dark:text-zinc-600 hover:text-amber-400' }}">
                            <flux:icon name="flag" variant="{{ $flagged ? 'solid' : 'outline' }}" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                {{-- Code block (if any) --}}
                @if($question->code_block)
                    <div class="px-6 pt-4">
                        <pre class="overflow-x-auto rounded-lg bg-zinc-950 p-4 text-sm"><code class="language-{{ $question->code_language }}">{{ $question->code_block }}</code></pre>
                    </div>
                @endif

                {{-- Answer area --}}
                <div class="px-6 py-5">
                    @switch($question->type)

                        {{-- ── MCQ (multi-select checkboxes) ── --}}
                        @case('mcq')
                            <div class="space-y-2.5">
                                @foreach($question->options as $option)
                                    @php($isChecked = $this->isOptionSelected($question->id, $option->id))
                                    <label wire:key="opt-{{ $option->id }}"
                                           class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition-colors
                                               {{ $isChecked
                                                   ? 'border-indigo-400 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-950/40'
                                                   : 'border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/50' }}">
                                        <input type="checkbox"
                                               wire:click="toggleOption({{ $question->id }}, {{ $option->id }})"
                                               @checked($isChecked)
                                               class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm leading-snug {{ $isChecked ? 'font-medium text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300' }}">
                                            {{ $option->option_text }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @break

                        {{-- ── True / False ── --}}
                        @case('true_false')
                            @php($tfOptions = $question->options->sortBy('order')->values())
                            <div class="flex gap-3">
                                @foreach($tfOptions as $tfOption)
                                    @php($isTfSelected = ($qAnswer['selected_option_id'] ?? null) === $tfOption->id)
                                    <button wire:click="saveAnswer({{ $question->id }}, {{ $tfOption->id }})"
                                            class="flex-1 rounded-lg border py-3 text-sm font-semibold transition-all
                                                {{ $isTfSelected
                                                    ? 'border-indigo-500 bg-indigo-500 text-white shadow-sm'
                                                    : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                                        {{ $tfOption->option_text }}
                                    </button>
                                @endforeach
                            </div>
                            @break

                        {{-- ── Short answer ── --}}
                        @case('short_answer')
                            <flux:textarea
                                wire:change="saveAnswer({{ $question->id }}, $event.target.value)"
                                rows="4"
                                class="w-full"
                                placeholder="Type your answer here...">{{ $qAnswer['text_answer'] ?? '' }}</flux:textarea>
                            @break

                        {{-- ── Code snippet ── --}}
                        @case('code_snippet')
                            <flux:textarea
                                wire:change="saveAnswer({{ $question->id }}, $event.target.value)"
                                rows="7"
                                class="w-full font-mono text-sm"
                                placeholder="Write your code here...">{{ $qAnswer['text_answer'] ?? '' }}</flux:textarea>
                            @break

                    @endswitch
                </div>

                {{-- Answered indicator strip --}}
                @if($answered)
                    <div class="flex items-center gap-1.5 border-t border-green-100 dark:border-green-900/40 px-6 py-2.5">
                        <flux:icon name="check-circle" variant="solid" class="h-4 w-4 text-green-500" />
                        <span class="text-xs font-medium text-green-600 dark:text-green-400">Answer saved</span>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Bottom submit button --}}
        <div class="mt-4 flex justify-end pb-16">
            <flux:button variant="primary" wire:click="$set('showSubmitModal', true)">
                Submit Exam
                <flux:icon name="arrow-right" class="ml-2 h-4 w-4" />
            </flux:button>
        </div>
    </div>

    {{-- ============================================================
         SUBMIT MODAL
    ============================================================ --}}
    @if($showSubmitModal)
        <div x-data="{ show: @entangle('showSubmitModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div @click.outside="$wire.set('showSubmitModal', false)"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <flux:heading level="3" size="lg">Submit Exam?</flux:heading>
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                    You have answered
                    <strong class="text-zinc-900 dark:text-zinc-100">{{ $answeredCount }}</strong>
                    of
                    <strong class="text-zinc-900 dark:text-zinc-100">{{ $totalCount }}</strong>
                    questions.
                </p>
                @if($totalCount - $answeredCount > 0)
                    <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-400">
                        {{ $totalCount - $answeredCount }} {{ Str::plural('question', $totalCount - $answeredCount) }} unanswered.
                    </p>
                @endif
                <div class="mt-6 flex gap-3">
                    <flux:button variant="ghost" wire:click="$set('showSubmitModal', false)" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button variant="primary" wire:click="submit" class="flex-1" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Confirm Submit</span>
                        <span wire:loading wire:target="submit">Submitting…</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         TAB SWITCH WARNING OVERLAY
    ============================================================ --}}
    @if($showTabWarning)
        <div x-data="{ show: @entangle('showTabWarning') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             class="fixed inset-0 z-50 flex items-center justify-center bg-amber-500/95 backdrop-blur-sm">
            <div class="mx-4 max-w-md text-center text-white">
                <flux:icon name="exclamation-triangle" class="mx-auto h-14 w-14" />
                <flux:heading level="2" size="xl" class="mt-4">Tab Switch Detected</flux:heading>
                <p class="mt-2">
                    This is tab switch <strong>{{ $tabSwitchCount }}</strong> of {{ $maxTabSwitches }}.
                    Your exam will be automatically submitted if you switch again.
                </p>
                <flux:button variant="primary" wire:click="dismissTabWarning" class="mt-6">
                    I Understand — Continue Exam
                </flux:button>
            </div>
        </div>
    @endif

    {{-- ============================================================
         IP CHANGE TOAST
    ============================================================ --}}
    @if($isIpChanged)
        <div class="fixed bottom-4 right-4 z-40 flex max-w-sm items-start gap-3 rounded-xl bg-amber-500 p-4 text-white shadow-lg">
            <flux:icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="text-sm">{{ $ipChangeWarning }}</p>
        </div>
    @endif

</div>