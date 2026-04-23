@extends('components.layouts.student')
@section('title', 'Student Dashboard')

@section('content')
    <div class="space-y-8">
        <!-- Available Exams -->
        <section>
            <flux:heading level="2" size="lg">Available Exams</flux:heading>

            @if($exams->isEmpty() && $attempted->isEmpty())
                <flux:card class="mt-4">
                    <div class="py-8 text-center">
                        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                        <p class="mt-4 text-zinc-600 dark:text-zinc-400">No exams available at this time.</p>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-500">Check back later for new exams.</p>
                    </div>
                </flux:card>
            @elseif($exams->isEmpty())
                <flux:card class="mt-4">
                    <div class="py-8 text-center">
                        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                        <p class="mt-4 text-zinc-600 dark:text-zinc-400">No exams available at this time.</p>
                    </div>
                </flux:card>
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($exams as $exam)
                        <flux:card>
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <flux:heading level="3" size="md">{{ $exam->title }}</flux:heading>
                                    <flux:badge variant="success">Available</flux:badge>
                                </div>

                                @if($exam->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">{{ $exam->description }}</p>
                                @endif

                                <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-500">
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="clock" class="h-4 w-4" />
                                        {{ $exam->duration_minutes }} min
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="question-mark-circle" class="h-4 w-4" />
                                        {{ $exam->questions_count }} questions
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="star" class="h-4 w-4" />
                                        {{ $exam->total_marks }} marks
                                    </span>
                                </div>

                                <div class="mt-2">
                                    <flux:button variant="primary" href="{{ route('student.exam.start', $exam) }}" class="w-full">
                                        Start Exam
                                    </flux:button>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Attempted Exams -->
        @if($attempted->isNotEmpty())
            <section>
                <flux:heading level="2" size="lg">Completed Exams</flux:heading>

                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($attempted as $session)
                        <flux:card>
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <flux:heading level="3" size="md">{{ $session->exam->title }}</flux:heading>
                                    <flux:badge variant="neutral">Submitted</flux:badge>
                                </div>

                                <div class="text-sm text-zinc-500 dark:text-zinc-500">
                                    <p>Completed: {{ $session->submitted_at?->format('M d, Y H:i') }}</p>
                                    <p>Score: {{ $session->score }}/{{ $session->total_marks }} ({{ $session->percentage }}%)</p>
                                </div>

                                <div class="mt-2">
                                    <flux:button variant="secondary" href="{{ route('student.results', $session) }}" class="w-full">
                                        View Results
                                    </flux:button>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection