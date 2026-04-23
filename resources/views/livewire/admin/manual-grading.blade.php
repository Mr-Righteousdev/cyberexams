<div>
    <flux:header>
        <flux:heading size="lg">Grade Answer</flux:heading>
        <flux:spacer />
        <flux:button variant="subtle" href="{{ route('admin.grading-queue') }}">
            <flux:icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to Queue
        </flux:button>
    </flux:header>

    {{-- Context Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <flux:card>
            <flux:heading size="sm">Exam</flux:heading>
            <flux:text>{{ $answer->session->exam->title }}</flux:text>
        </flux:card>

        <flux:card>
            <flux:heading size="sm">Student</flux:heading>
            <flux:text>{{ $answer->session->user->name }}</flux:text>
            <flux:text size="sm" class="text-gray-500">{{ $answer->session->user->email }}</flux:text>
        </flux:card>
    </div>

    {{-- Question --}}
    <flux:card class="mb-6">
        <flux:heading size="md" class="mb-2">Question</flux:heading>
        <div class="text-gray-700 mb-2">{{ $answer->question->question_text }}</div>
        <flux:badge variant="neutral">{{ $answer->question->marks }} marks</flux:badge>
    </flux:card>

    {{-- Student's Answer --}}
    <flux:card class="mb-6">
        <flux:heading size="md" class="mb-2">Student's Answer</flux:heading>
        <div class="p-4 bg-gray-50 border rounded-lg">
            {{ $answer->text_answer ?? '(No answer provided)' }}
        </div>
    </flux:card>

    {{-- Grading Form --}}
    <flux:card>
        <flux:heading size="md" class="mb-4">Assign Marks</flux:heading>

        <form wire:submit.prevent="grade">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Marks Awarded (0 - {{ $answer->question->marks }})</flux:label>
                    <flux:input
                        wire:model="marks"
                        type="number"
                        min="0"
                        max="{{ $answer->question->marks }}"
                        step="0.5"
                    />
                    <flux:error name="marks" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="grade">Saving...</span>
                        <span wire:target="grade">Save Grade</span>
                    </flux:button>

                    @if($graded)
                        <flux:badge variant="success">Graded!</flux:badge>
                    @endif
                </div>
            </div>
        </form>

        {{-- Quick Grade Buttons --}}
        <div class="mt-6 pt-4 border-t">
            <div class="text-sm text-gray-500 mb-2">Quick grade:</div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="outline" wire:click="$set('marks', {{ $answer->question->marks }})">
                    Full Marks ({{ $answer->question->marks }})
                </flux:button>
                <flux:button size="sm" variant="outline" wire:click="$set('marks', {{ round($answer->question->marks * 0.5, 1) }})">
                    Partial ({{ round($answer->question->marks * 0.5, 1) }})
                </flux:button>
                <flux:button size="sm" variant="outline" wire:click="$set('marks', 0)">
                    Zero (0)
                </flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Back to session results --}}
    <div class="mt-4">
        <flux:button variant="subtle" href="{{ route('admin.session-results', $answer->session_id) }}">
            View Full Session Results
        </flux:button>
    </div>
</div>