<div>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="mb-6">
                <a href="{{ route('admin.students.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    ← Back to Students
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $student->name }}
                    </h2>
                    <div class="flex items-center gap-2">
                        @if($student->is_active)
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Inactive</span>
                        @endif
                    </div>
                </div>

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>Name</flux:label>
                            <flux:input wire:model="name" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email</flux:label>
                            <flux:input wire:model="email" type="email" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Student Number</flux:label>
                            <flux:input wire:model="student_number" placeholder="Optional" />
                            <flux:error name="student_number" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Status</flux:label>
                            <flux:switch wire:model="is_active">
                                {{ $is_active ? 'Active' : 'Inactive' }}
                            </flux:switch>
                        </flux:field>

                        <flux:field>
                            <flux:label>Password</flux:label>
                            <flux:input :value="'password'" disabled class="bg-gray-100" />
                            <flux:description>Default password. Click reset to change.</flux:description>
                        </flux:field>

                        <div class="flex justify-between pt-4 border-t">
                            <flux:button type="button" wire:click="resetPassword" variant="primary" color="amber">
                                Reset Password
                            </flux:button>

                            <flux:button type="submit">
                                Save Changes
                            </flux:button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Student Info</h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Created:</dt>
                        <dd class="text-gray-900">{{ $student->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Role:</dt>
                        <dd class="text-gray-900 capitalize">{{ $student->role }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Exam Sessions:</dt>
                        <dd class="text-gray-900">{{ $student->examSessions->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
