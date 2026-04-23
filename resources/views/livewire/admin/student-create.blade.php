<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="mb-6">
                <a href="{{ route('admin.students.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    ← Back to Students
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex gap-4 mb-6">
                    <button wire:click="$set('is_bulk', false)" class="px-4 py-2 rounded-md {{ ! $is_bulk ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        Single Student
                    </button>
                    <button wire:click="$set('is_bulk', true)" class="px-4 py-2 rounded-md {{ $is_bulk ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        Bulk Import
                    </button>
                </div>

                @if (! $is_bulk)
                    <form wire:submit.prevent="saveSingle">
                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="student_number" class="block text-sm font-medium text-gray-700">Student Number (optional)</label>
                                <input type="text" id="student_number" wire:model="student_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <button type="submit" class="mt-6 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Create Student
                        </button>
                    </form>
                @else
                    <form wire:submit.prevent="saveBulk">
                        <div>
                            <label for="manual_students" class="block text-sm font-medium text-gray-700">Students (one per line)</label>
                            <p class="text-xs text-gray-500 mb-2">Format: email, name, student_number (optional)</p>
                            <textarea id="manual_students" wire:model="manual_students" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="john@example.com, John Doe, STU001&#10;jane@example.com, Jane Smith, STU002"></textarea>
                        </div>

                        <button type="submit" class="mt-6 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Import Students
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>