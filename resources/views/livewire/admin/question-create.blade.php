<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="mb-6">
                <a href="{{ route('admin.exams.questions.index', $exam) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    ← Back to Questions
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    {{ $question ? 'Edit Question' : 'Add Question' }}
                </h2>

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Question Type</label>
                            <select id="type" wire:model.live="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="mcq">Multiple Choice (MCQ)</option>
                                <option value="true_false">True / False</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="code_snippet">Code Snippet</option>
                            </select>
                        </div>

                        <div>
                            <label for="question_text" class="block text-sm font-medium text-gray-700">Question Text</label>
                            <textarea id="question_text" wire:model="question_text" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        @if (in_array($type, ['mcq', 'true_false', 'code_snippet']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                                @if ($type === 'true_false')
                                    <div class="space-y-2">
                                        @foreach ($options as $index => $option)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="correct_option" wire:model.live="selectedCorrect" value="{{ $index }}" class="w-4 h-4 text-indigo-600">
                                                <span class="text-sm">{{ $option['option_text'] }}</span>
                                            </label>
                                        @endforeach
                                        @error('selectedCorrect')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($options as $index => $option)
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" wire:model="options.{{ $index }}.is_correct" value="1" class="w-4 h-4 text-indigo-600 rounded">
                                                <input type="text" wire:model="options.{{ $index }}.option_text" placeholder="Option {{ $index + 1 }}" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                @if(count($options) > 2)
                                                    <button type="button" wire:click="removeOption({{ $index }})" class="text-red-600 hover:text-red-900">✕</button>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if (count($options) < 6)
                                            <button type="button" wire:click="addOption()" class="text-sm text-indigo-600 hover:text-indigo-900">+ Add Option</button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($type === 'code_snippet')
                            <div>
                                <label for="code_block" class="block text-sm font-medium text-gray-700">Code Block (optional)</label>
                                <textarea id="code_block" wire:model="code_block" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono" placeholder="// Code to display..."></textarea>
                            </div>

                            <div>
                                <label for="code_language" class="block text-sm font-medium text-gray-700">Language</label>
                                <select id="code_language" wire:model="code_language" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select language</option>
                                    <option value="python">Python</option>
                                    <option value="javascript">JavaScript</option>
                                    <option value="php">PHP</option>
                                    <option value="java">Java</option>
                                    <option value="cpp">C++</option>
                                    <option value="csharp">C#</option>
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="marks" class="block text-sm font-medium text-gray-700">Marks</label>
                            <input type="number" id="marks" wire:model="marks" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="explanation" class="block text-sm font-medium text-gray-700">Explanation (shown after submit)</label>
                            <textarea id="explanation" wire:model="explanation" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('admin.exams.questions.index', $exam) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            ← Back to Questions
                        </a>
                        <div class="flex gap-2">
                            <button type="button"
                                    wire:click="saveAndContinue"
                                    wire:loading.attr="disabled"
                                    wire:loading.class.add("opacity-50 cursor-not-allowed")
                                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading wire:target="saveAndContinue">Saving...</span>
                                <span wire:target="saveAndContinue">Save & Add Another</span>
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class.add("opacity-50 cursor-not-allowed")
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading wire:target="save">Adding...</span>
                                <span wire:target="save">{{ $question ? 'Update' : 'Save' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
