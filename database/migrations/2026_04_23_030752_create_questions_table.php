<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mcq', 'true_false', 'short_answer', 'code_snippet'])->default('mcq');
            $table->text('question_text');
            $table->text('code_block')->nullable();
            $table->string('code_language', 50)->nullable();
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
