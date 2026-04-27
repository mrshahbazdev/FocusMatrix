<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE delegations MODIFY COLUMN status ENUM('open','invited','accepted','declined','in_progress','done','overdue','cancelled') NOT NULL DEFAULT 'open'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE delegations MODIFY COLUMN status ENUM('open','in_progress','done','overdue','cancelled') NOT NULL DEFAULT 'open'");
        }
    }
};
