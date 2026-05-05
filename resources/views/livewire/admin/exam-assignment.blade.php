<div>
    <flux:header>
        <flux:heading size="lg">Exam Assignments</flux:heading>
        <flux:spacer />
        <flux:button variant="subtle" href="{{ route('admin.exams.index') }}">
            <flux:icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to Exams
        </flux:button>
    </flux:header>

    <div class="space-y-6">
        <!-- Exam Info -->
        <flux:card>
            <flux:heading size="md">{{ $exam->title }}</flux:heading>
            <flux:text>{{ $exam->questions()->count() }} questions</flux:text>
        </flux:card>

        <!-- Access Mode -->
        <flux:card>
            <flux:heading size="sm" class="mb-4">Access Mode</flux:heading>

            <div class="flex gap-4 mb-4">
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="accessMode" value="open" class="w-4 h-4">
                    <span>Open to all students</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="accessMode" value="restricted" class="w-4 h-4">
                    <span>Restricted - specific students only</span>
                </label>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="allowRepeatAfterFail" class="w-4 h-4 rounded">
                    <span>Allow re-take after failing</span>
                </label>
            </div>

            <flux:button wire:click="saveAccessMode" class="mt-4">
                Save Access Settings
            </flux:button>
        </flux:card>

        <!-- CSV Upload -->
        @if($accessMode === 'restricted')
            <flux:card>
                <flux:heading size="sm" class="mb-4">Upload from CSV</flux:heading>
                <flux:text size="sm" class="mb-2">Upload a CSV file with a "student_number" column.</flux:text>

                <div class="flex items-center gap-4">
                    <input type="file" wire:model="csvFile" accept=".csv" class="text-sm">
                    <flux:button wire:click="uploadCsv" :disabled="!$csvFile">
                        Upload
                    </flux:button>
                </div>
            </flux:card>
        @endif

        <!-- Student List -->
        @if($accessMode === 'restricted')
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">
                        Assigned Students ({{ $assignmentCount }} selected)
                    </flux:heading>
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="outline" wire:click="selectAll">
                            Select All
                        </flux:button>
                        <flux:button size="sm" variant="outline" wire:click="clearAll">
                            Clear All
                        </flux:button>
                    </div>
                </div>

                @if($students->isEmpty())
                    <flux:text>No students found in the system.</flux:text>
                @else
                    <div class="max-h-96 overflow-y-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Number</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($students as $student)
                                    <tr class="{{ in_array($student->id, $selectedStudents) ? 'bg-indigo-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                {{ in_array($student->id, $selectedStudents) ? 'checked' : '' }}
                                                wire:click="toggleStudent({{ $student->id }})"
                                                class="w-4 h-4 rounded text-indigo-600"
                                            >
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $student->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $student->email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $student->student_number ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </flux:card>
        @endif
    </div>
</div>
