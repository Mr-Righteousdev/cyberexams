<div>
    @if ($show)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('show', false)">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ $results['skipped_invalid'] + ($results['skipped_duplicate'] ?? 0) > 0 ? 'Import Completed with Issues' : 'Import Complete' }}
                            </h3>
                            @if ($examTitle)
                                <p class="text-sm text-gray-500 mt-1">{{ $examTitle }}</p>
                            @endif
                        </div>
                        <button wire:click="$set('show', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-700">{{ $results['total'] }}</div>
                            <div class="text-xs text-blue-600 mt-1">Total Received</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-700">{{ $results['imported'] }}</div>
                            <div class="text-xs text-green-600 mt-1">Imported</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-700">{{ ($results['skipped_invalid'] ?? 0) + ($results['skipped_duplicate'] ?? 0) }}</div>
                            <div class="text-xs text-red-600 mt-1">Skipped</div>
                        </div>
                    </div>

                    @if (!empty($results['by_type']))
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Imported by Type</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($results['by_type'] as $type => $count)
                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-full">
                                        {{ str_replace('_', ' ', ucfirst($type)) }}: {{ $count }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!empty($results['failures']))
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-red-700 mb-2">Failed Questions ({{ count($results['failures']) }})</h4>
                            <div class="max-h-48 overflow-y-auto space-y-2">
                                @foreach ($results['failures'] as $failure)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-800 truncate flex-1 mr-2">
                                                {{ Str::limit($failure['question_text'], 60) }}
                                            </span>
                                            <span class="text-red-600 shrink-0">{{ $failure['type'] }}</span>
                                        </div>
                                        <div class="text-red-600 mt-1">{{ $failure['error'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <button wire:click="downloadFailures" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800">
                                Download failed questions as JSON
                            </button>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <button wire:click="$set('show', false)" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>