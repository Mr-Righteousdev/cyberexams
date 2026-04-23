<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'student'])->default('student')->after('password');
            $table->string('student_number', 100)->nullable()->unique()->after('role');
            $table->boolean('is_active')->default(true)->after('student_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'student_number', 'is_active']);
        });
    }
};
