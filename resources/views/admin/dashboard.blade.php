<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ExamShield</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-2 text-gray-600">Welcome to ExamShield</p>
            <div class="mt-4 space-x-4">
                <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900">Manage Exams</a>
                <a href="{{ route('admin.students.index') }}" class="text-indigo-600 hover:text-indigo-900">Manage Students</a>
                <a href="{{ route('admin.results.index') }}" class="text-indigo-600 hover:text-indigo-900">View Results</a>
            </div>
        </div>
    </div>
</body>
</html>