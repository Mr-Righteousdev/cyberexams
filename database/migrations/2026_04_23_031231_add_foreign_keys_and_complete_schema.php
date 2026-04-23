<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exam_sessions')) {
            Schema::create('exam_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('ip_address', 45);
                $table->timestamp('started_at');
                $table->timestamp('submitted_at')->nullable();
                $table->boolean('is_submitted')->default(false);
                $table->boolean('is_flagged')->default(false);
                $table->text('flag_reason')->nullable();
                $table->integer('tab_switch_count')->default(0);
                $table->decimal('score', 8, 2)->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->boolean('passed')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('answers')) {
            Schema::create('answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained()->cascadeOnDelete();
                $table->foreignId('selected_option_id')->nullable()->constrained('options')->nullOnDelete();
                $table->text('text_answer')->nullable();
                $table->boolean('is_correct')->nullable();
                $table->decimal('marks_awarded', 5, 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();
                $table->enum('event_type', ['tab_switch', 'copy_attempt', 'paste_attempt', 'right_click', 'ip_change', 'devtools_open', 'focus_lost', 'auto_submit']);
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('exam_sessions');
    }
};
