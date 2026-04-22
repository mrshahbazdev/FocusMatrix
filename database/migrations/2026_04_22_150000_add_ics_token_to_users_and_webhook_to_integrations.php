<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ics_token', 64)->nullable()->unique()->after('remember_token');
        });

        Schema::table('integrations', function (Blueprint $table) {
            // Reusing meta JSON for webhook_url — no new column needed
            // but add a friendly label column for user-named webhooks
            $table->string('label')->nullable()->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ics_token');
        });
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
