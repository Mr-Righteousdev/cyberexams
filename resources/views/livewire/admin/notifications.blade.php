<div>
    <flux:header>
        <flux:heading size="lg">Notifications</flux:heading>
    </flux:header>

    <flux:card>
        @if($notifications->isEmpty())
            <flux:callout variant="warning">No notifications yet.</flux:callout>
        @else
            <div class="space-y-2">
                @foreach($notifications as $notification)
                    <div class="flex items-start gap-3 p-3 rounded-lg {{ $notification->read_at ? 'bg-zinc-50 dark:bg-zinc-800' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if($notification->type === \App\Notifications\ImportQuestionsComplete::class)
                                    <flux:icon name="arrow-up-tray" class="w-5 h-5 text-indigo-600" />
                                    <flux:heading size="sm">Import Complete</flux:heading>
                                @else
                                    <flux:icon name="bell" class="w-5 h-5 text-zinc-600" />
                                    <flux:heading size="sm">{{ Str::limit(class_basename($notification->type), 30) }}</flux:heading>
                                @endif
                            </div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($notification->type === \App\Notifications\ImportQuestionsComplete::class)
                                    <p>
                                        Imported {{ $notification->data['imported'] ?? 0 }} of {{ $notification->data['total'] ?? 0 }} questions
                                        @if(($notification->data['skipped_invalid'] ?? 0) + ($notification->data['skipped_duplicate'] ?? 0) > 0)
                                            ({{ ($notification->data['skipped_invalid'] ?? 0) + ($notification->data['skipped_duplicate'] ?? 0) }} skipped)
                                        @endif
                                        into <strong>{{ $notification->data['exam_title'] ?? 'exam' }}</strong>.
                                    </p>
                                    <button wire:click="openImportNotification('{{ $notification->id }}')" class="text-xs text-indigo-600 hover:text-indigo-800 underline mt-1">
                                        View details
                                    </button>
                                @else
                                    <p>Notification data: {{ json_encode($notification->data) }}</p>
                                @endif
                            </div>
                            <div class="text-xs text-zinc-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @if(!$notification->read_at)
                            <flux:badge size="sm" color="blue">New</flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <div class="mt-4 pt-4 border-t">
                    <flux:button wire:click="markAllRead" variant="outline" size="sm">
                        Mark all as read
                    </flux:button>
                </div>
            @endif
        @endif
    </flux:card>

    @if($showModal)
        <flux:modal :open="$showModal" class="max-w-lg">
            <flux:heading size="lg">
                {{ $modalData['skipped_invalid'] + ($modalData['skipped_duplicate'] ?? 0) > 0 ? 'Import Completed with Issues' : 'Import Complete' }}
            </flux:heading>

            <div class="mt-4">
                @if(!empty($modalData['exam_title']))
                    <flux:text class="mb-4">{{ $modalData['exam_title'] }}</flux:text>
                @endif

                <div class="grid grid-cols-3 gap-3 mb-4">
                    <flux:card class="text-center">
                        <flux:heading size="lg">{{ $modalData['total'] ?? 0 }}</flux:heading>
                        <flux:text size="xs">Total</flux:text>
                    </flux:card>
                    <flux:card class="text-center">
                        <flux:heading size="lg" class="text-green-600">{{ $modalData['imported'] ?? 0 }}</flux:heading>
                        <flux:text size="xs">Imported</flux:text>
                    </flux:card>
                    <flux:card class="text-center">
                        <flux:heading size="lg" class="text-red-600">
                            {{ ($modalData['skipped_invalid'] ?? 0) + ($modalData['skipped_duplicate'] ?? 0) }}
                        </flux:heading>
                        <flux:text size="xs">Skipped</flux:text>
                    </flux:card>
                </div>

                @if(!empty($modalData['by_type']))
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($modalData['by_type'] as $type => $count)
                            <flux:badge size="sm">{{ str_replace('_', ' ', ucfirst($type)) }}: {{ $count }}</flux:badge>
                        @endforeach
                    </div>
                @endif

                @if(!empty($modalData['failures']))
                    <div class="mb-4">
                        <flux:heading size="sm" class="text-red-600 mb-2">Failed ({{ count($modalData['failures']) }})</flux:heading>
                        <div class="max-h-48 overflow-y-auto space-y-2">
                            @foreach($modalData['failures'] as $failure)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-2 text-xs">
                                    <div class="font-medium text-red-800 dark:text-red-300 truncate">{{ Str::limit($failure['question_text'], 60) }}</div>
                                    <div class="text-red-600 dark:text-red-400">{{ $failure['error'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <flux:heading size="sm">Exam Stats</flux:heading>
            <div class="mt-2 grid grid-cols-2 gap-3 mb-4">
                <flux:card padding="p-3">
                    <flux:heading size="sm">Before</flux:heading>
                    <flux:text size="lg" class="font-bold">{{ ($modalData['total'] ?? 0) - ($modalData['imported'] ?? 0) }}</flux:text>
                    <flux:text size="xs">Questions</flux:text>
                </flux:card>
                <flux:card padding="p-3">
                    <flux:heading size="sm">After</flux:heading>
                    <flux:text size="lg" class="font-bold">{{ ($modalData['total'] ?? 0) - ($modalData['skipped_invalid'] + ($modalData['skipped_duplicate'] ?? 0) - ($modalData['imported'] ?? 0)) }}</flux:text>
                    <flux:text size="xs">Questions</flux:text>
                </flux:card>
            </div>

            <flux:heading size="sm">Results</flux:heading>
            <div class="mt-2 grid grid-cols-2 gap-3 mb-4">
                <flux:card padding="p-3">
                    <flux:heading size="sm">Pass</flux:heading>
                    <flux:text size="lg" class="font-bold text-green-600">{{ $modalData['pass_count'] ?? '-' }}</flux:text>
                </flux:card>
                <flux:card padding="p-3">
                    <flux:heading size="sm">Fail</flux:heading>
                    <flux:text size="lg" class="font-bold text-red-600">{{ $modalData['fail_count'] ?? '-' }}</flux:text>
                </flux:card>
            </div>

            <flux:heading size="sm">Question Types Breakdown</flux:heading>
            <div class="mt-2 grid grid-cols-2 gap-3 mb-4">
                @if(!empty($modalData['by_type']))
                    @foreach($modalData['by_type'] as $type => $count)
                        <flux:card padding="p-3">
                            <flux:heading size="sm">{{ str_replace('_', ' ', ucfirst($type)) }}</flux:heading>
                            <flux:text size="lg" class="font-bold">{{ $count }}</flux:text>
                            <flux:text size="xs">Added</flux:text>
                        </flux:card>
                    @endforeach
                @else
                    <flux:text size="sm" class="text-zinc-500 col-span-2">No type breakdown available</flux:text>
                @endif
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button variant="outline" wire:click="$set('showModal', false)">Close</flux:button>
                <flux:button wire:click="$set('showModal', false)" :href="$modalData['exam_id'] ? route('admin.exams.stats', $modalData['exam_id']) : null">
                    View Exam Stats
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>