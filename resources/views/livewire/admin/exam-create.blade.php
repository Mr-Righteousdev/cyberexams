<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="mb-6">
                <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    ← Back to Exams
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    {{ $exam ? 'Edit Exam' : 'Create Exam' }}
                </h2>

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="title" wire:model="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div>
                            <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions</label>
                            <textarea id="instructions" wire:model="instructions" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Instructions shown to students before starting the exam..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                                <input type="number" id="duration_minutes" wire:model="duration_minutes" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            {{-- <div>
                                <label for="total_marks" class="block text-sm font-medium text-gray-700">Total Marks</label>
                                <input type="number" id="total_marks" wire:model="total_marks" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div> --}}
                        </div>

                        <div>
                            <label for="passing_percentage" class="block text-sm font-medium text-gray-700">Passing Percentage (optional)</label>
                            <input type="number" id="passing_percentage" wire:model="passing_percentage" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. 50 (leave empty for 50%)">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="starts_at" class="block text-sm font-medium text-gray-700">Start Time (optional)</label>
                                <input type="datetime-local" id="starts_at" wire:model="starts_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="ends_at" class="block text-sm font-medium text-gray-700">End Time (optional)</label>
                                <input type="datetime-local" id="ends_at" wire:model="ends_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="shuffle_questions" wire:model="shuffle_questions" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="shuffle_questions" class="ml-2 block text-sm text-gray-700">Shuffle questions for each student</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="shuffle_options" wire:model="shuffle_options" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="shuffle_options" class="ml-2 block text-sm text-gray-700">Shuffle answer options</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_published" wire:model="is_published" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_published" class="ml-2 block text-sm text-gray-700">Publish exam immediately</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('admin.exams.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class.add("opacity-50 cursor-not-allowed")
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading wire:target="save">Saving...</span>
                            <span wire:target="save">{{ $exam ? 'Update' : 'Create' }} Exam</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
