<div>
    <flux:header>
        <flux:heading size="lg">Session Details</flux:heading>
        <flux:spacer />
        <flux:button variant="subtle" href="{{ route('admin.exam-results', $session->exam_id) }}">
            <flux:icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to Results
        </flux:button>
    </flux:header>

    {{-- Student Info Card --}}
    <flux:card class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <flux:heading size="md">{{ $session->user->name }}</flux:heading>
                <flux:text>{{ $session->user->email }}</flux:text>
                @if($session->user->student_number)
                    <flux:text>Student #: {{ $session->user->student_number }}</flux:text>
                @endif
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold {{ $session->percentage >= 60 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $session->percentage ?? 0 }}%
                </div>
                <div class="text-gray-500">
                    {{ $session->score ?? 0 }} / {{ $session->total_marks ?? 0 }} marks
                </div>
                @if($session->passed === true)
                    <flux:badge variant="success" size="lg">PASS</flux:badge>
                @elseif($session->passed === false)
                    <flux:badge variant="danger" size="lg">FAIL</flux:badge>
                @else
                    <flux:badge variant="neutral" size="lg">PENDING REVIEW</flux:badge>
                @endif
            </div>
        </div>

        <div class="mt-4 pt-4 border-t grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Started:</span>
                <div>{{ $session->started_at?->format('M j, Y H:i') }}</div>
            </div>
            <div>
                <span class="text-gray-500">Submitted:</span>
                <div>{{ $session->submitted_at?->format('M j, Y H:i') }}</div>
            </div>
            <div>
                <span class="text-gray-500">Time Taken:</span>
                <div>
                    @if($session->started_at && $session->submitted_at)
                        {{ $session->started_at->diffInMinutes($session->submitted_at) }} min
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div>
                <span class="text-gray-500">IP Address:</span>
                <div>{{ $session->ip_address }}</div>
            </div>
        </div>

        @if($session->is_flagged)
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="font-medium text-yellow-800">Flagged</div>
                <div class="text-sm text-yellow-700">{{ $session->flag_reason }}</div>
            </div>
        @endif
    </flux:card>

    {{-- Answers Breakdown --}}
    <flux:card>
        <flux:heading size="md" class="mb-4">Answers</flux:heading>

        <div class="space-y-4">
            @foreach($session->answers()->orderBy('id')->get() as $index => $answer)
                @php $question = $answer->question; @endphp
                <div class="p-4 border rounded-lg {{ $answer->is_correct === true ? 'border-green-200 bg-green-50' : ($answer->is_correct === false ? 'border-red-200 bg-red-50' : 'border-yellow-200 bg-yellow-50') }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-medium">Q{{ $index + 1 }}: {{ Str::limit($question->question_text, 80) }}</div>
                        <div class="text-sm">
                            <span class="text-gray-500">{{ $question->marks }} marks</span>
                            @if($answer->marks_awarded !== null)
                                <span class="ml-2 font-medium">→ {{ $answer->marks_awarded }}</span>
                            @elseif($question->type === 'short_answer' || $question->type === 'code_snippet')
                                <flux:badge variant="warning" size="sm">NEEDS REVIEW</flux:badge>
                            @endif
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 mb-2">
                        <span class="font-medium">Type:</span> {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                    </div>

                    @if($question->type === 'mcq' || $question->type === 'true_false')
                        <div class="space-y-1">
                            @foreach($question->options as $option)
                                @php
                                    $isSelected = $answer->selected_option_id === $option->id;
                                    $isCorrectOption = $option->is_correct;
                                @endphp
                                <div class="flex items-center gap-2 text-sm p-2 rounded
                                    {{ $isCorrectOption ? 'bg-green-100 text-green-800' : ($isSelected ? 'bg-red-100 text-red-800' : 'bg-gray-50 text-gray-600') }}">
                                    <span class="w-4">
                                        @if($isCorrectOption)
                                            <flux:icon name="check-circle" class="w-4 h-4 text-green-600" />
                                        @elseif($isSelected)
                                            <flux:icon name="x-circle" class="w-4 h-4 text-red-600" />
                                        @else
                                            <flux:icon name="circle" class="w-4 h-4" />
                                        @endif
                                    </span>
                                    <span class="{{ $isSelected ? 'font-medium' : '' }}">
                                        {{ $option->option_text }}
                                    </span>
                                    @if($isSelected && $isCorrectOption)
                                        <span class="text-green-600">(Your Answer)</span>
                                    @elseif($isSelected)
                                        <span class="text-red-600">(Your Answer)</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type === 'short_answer' || $question->type === 'code_snippet')
                        <div class="mt-2">
                            <div class="text-sm font-medium text-gray-500">Student's Answer:</div>
                            <div class="p-3 bg-white border rounded text-sm">
                                {{ $answer->text_answer ?? '(No answer provided)' }}
                            </div>
                            @if(in_array($question->type, ['short_answer', 'code_snippet']) && $answer->marks_awarded === null)
                                <flux:button size="sm" variant="outline" class="mt-2" href="{{ route('admin.manual-grading-answer', $answer->id) }}">
                                    Grade This Answer
                                </flux:button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </flux:card>
</div>