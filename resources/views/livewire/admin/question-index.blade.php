<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Exams</a>
                    <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $exam->title }} — Questions</h2>
                </div>
                <a href="{{ route('admin.exams.questions.create', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    Add Question
                </a>
            </div>

            @if ($questions->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500">No questions yet. Add your first question.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($questions as $question)
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                            {{ str_replace('_', ' ', $question->type) }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ $question->marks }} marks</span>
                                    </div>
                                    <p class="text-gray-900">{{ $question->question_text }}</p>
                                    @if ($question->code_block)
                                        <pre class="mt-2 p-2 bg-gray-100 rounded text-sm overflow-x-auto"><code>{{ $question->code_block }}</code></pre>
                                    @endif
                                    @if ($question->options->isNotEmpty())
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($question->options as $option)
                                                <li class="text-sm {{ $option->is_correct ? 'text-green-600 font-medium' : 'text-gray-600' }}">
                                                    {{ $option->option_text }} {{ $option->is_correct ? '✓' : '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.exams.questions.edit', [$exam, $question]) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <button wire:click="delete({{ $question->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this question?')">Delete</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>