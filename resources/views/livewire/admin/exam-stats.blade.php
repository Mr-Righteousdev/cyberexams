<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Exams</a>
                    <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $exam->title }} — Stats & Import/Export</h2>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        View Questions
                    </a>
                    <a href="{{ route('admin.exams.questions.create', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        Add Question
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_questions'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Questions</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_marks'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Marks</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_sessions'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Sessions</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['completed_sessions'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Completed</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-3xl font-bold text-gray-900">
                        {{ $stats['pass_count'] }}/{{ $stats['fail_count'] }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">Pass / Fail</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Questions by Type</h3>
                    @if (empty($stats['questions_by_type']))
                        <p class="text-gray-500 text-sm">No questions yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($stats['questions_by_type'] as $type => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">{{ str_replace('_', ' ', ucfirst($type)) }}</span>
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-sm rounded-full">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Completed Sessions</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">{{ $stats['completed_sessions'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Active Sessions</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">{{ $stats['active_sessions'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Average Score</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                {{ $stats['avg_score'] ? round($stats['avg_score'], 1) . '%' : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Pass Rate</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                                @if ($stats['completed_sessions'] > 0)
                                    {{ round(($stats['pass_count'] / $stats['completed_sessions']) * 100, 1) }}%
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Passing Criteria</h3>
                <div class="flex items-center gap-6">
                    <div>
                        <span class="text-sm text-gray-500">Passing Percentage</span>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $exam->passing_percentage ?? 50 }}%
                        </div>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Total Marks Available</span>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total_marks'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Import / Export</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admin.exams.export', $exam) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Questions (JSON)
                    </a>
                    <a href="{{ route('admin.exams.sample-template', $exam) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Sample Template
                    </a>
                </div>

                <div class="mt-6 border-t pt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Import Questions from JSON</h4>
                    <form action="{{ route('admin.exams.import', $exam) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select JSON file</label>
                            <input type="file" name="questions_file" accept=".json" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                            Import Questions
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>