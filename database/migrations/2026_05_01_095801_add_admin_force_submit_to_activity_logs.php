<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE activity_logs MODIFY COLUMN event_type ENUM('tab_switch', 'copy_attempt', 'paste_attempt', 'right_click', 'ip_change', 'devtools_open', 'focus_lost', 'auto_submit', 'admin_force_submit') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE activity_logs MODIFY COLUMN event_type ENUM('tab_switch', 'copy_attempt', 'paste_attempt', 'right_click', 'ip_change', 'devtools_open', 'focus_lost', 'auto_submit') NOT NULL");
    }
};
