<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Exams</a>
                    <h2 class="text-2xl font-bold text-gray-900 mt-2">
                        {{ $exam->title }} — Questions
                        <a href="{{ route('admin.exams.stats', $exam) }}" class="text-sm font-normal text-indigo-500 hover:text-indigo-700 ml-2">(Stats & Import/Export)</a>
                    </h2>
                </div>
                <a href="{{ route('admin.exams.questions.create', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    Add Question
                </a>
            </div>

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-4">
                    <select wire:model.live="typeFilter" class="rounded-md border-gray-300 text-sm">
                        <option value="">All Types</option>
                        <option value="mcq">MCQ</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="code_snippet">Code Snippet</option>
                    </select>
                    <span class="text-sm text-gray-500">{{ $questions->count() }} questions</span>
                </div>
                <div class="flex rounded-md shadow-sm" role="group">
                    <button wire:click="$set('viewMode', 'list')" class="px-4 py-2 text-sm font-medium {{ $viewMode === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 rounded-l-md">
                        List
                    </button>
                    <button wire:click="$set('viewMode', 'cards')" class="px-4 py-2 text-sm font-medium {{ $viewMode === 'cards' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-t border-b border-r border-gray-300 rounded-r-md">
                        Cards
                    </button>
                </div>
            </div>

            @if ($questions->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500">No questions yet. Add your first question.</p>
                </div>
            @elseif ($viewMode === 'list')
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
                                    <button wire:click="delete({{ $question->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($questions as $question)
                        <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                    {{ str_replace('_', ' ', $question->type) }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $question->marks }} marks</span>
                            </div>
                            <p class="text-gray-900 text-sm flex-1">{{ $question->question_text }}</p>
                            @if ($question->code_block)
                                <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto"><code>{{ $question->code_block }}</code></pre>
                            @endif
                            @if ($question->options->isNotEmpty())
                                <ul class="mt-2 space-y-1">
                                    @foreach ($question->options as $option)
                                        <li class="text-xs {{ $option->is_correct ? 'text-green-600 font-medium' : 'text-gray-600' }}">
                                            {{ $option->option_text }} {{ $option->is_correct ? '✓' : '' }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <div class="flex justify-end gap-2 mt-3 pt-3 border-t">
                                <a href="{{ route('admin.exams.questions.edit', [$exam, $question]) }}" class="text-xs text-indigo-600 hover:text-indigo-900">Edit</a>
                                <button wire:click="delete({{ $question->id }})" class="text-xs text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
