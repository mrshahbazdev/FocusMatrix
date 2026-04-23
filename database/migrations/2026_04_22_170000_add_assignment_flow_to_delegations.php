<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delegations', function (Blueprint $table) {
            $table->timestamp('invited_at')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('invited_at');
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
            $table->text('decline_reason')->nullable()->after('declined_at');
            $table->foreignId('original_owner_id')->nullable()->after('delegator_id')
                ->constrained('users')->nullOnDelete();
            $table->string('invite_token', 64)->nullable()->unique()->after('decline_reason');
        });
    }

    public function down(): void
    {
        Schema::table('delegations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('original_owner_id');
            $table->dropColumn(['invited_at', 'accepted_at', 'declined_at', 'decline_reason', 'invite_token']);
        });
    }
};
