<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ExamShield - Student' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 dark:bg-zinc-900">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="border-b border-zinc-200 bg-white dark:bg-zinc-800">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        <span class="text-xl font-semibold text-zinc-900 dark:text-white">ExamShield</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:button type="submit" variant="ghost" size="sm">Logout</flux:button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>