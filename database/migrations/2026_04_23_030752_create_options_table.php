<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('options', function (Blueprint $table) {
            if (! Schema::hasColumn('options', 'question_id')) {
                $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropForeign(['options_question_id_foreign']);
            $table->dropColumn('question_id');
        });
    }
};
