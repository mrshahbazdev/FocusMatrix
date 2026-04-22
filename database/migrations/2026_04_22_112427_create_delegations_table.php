<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delegator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('delegate_name_fallback')->nullable();
            $table->text('goal');
            $table->enum('decision_scope', ['inform', 'consult', 'decide'])->default('consult');
            $table->timestamp('deadline')->nullable();
            $table->text('resources')->nullable();
            $table->json('inform_list')->nullable();
            $table->boolean('no_micromanagement')->default(true);
            $table->enum('status', ['open', 'in_progress', 'done', 'overdue', 'cancelled'])->default('open');
            $table->integer('health_score')->default(100);
            $table->timestamp('last_checkin_at')->nullable();
            $table->timestamps();
            $table->index(['delegator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegations');
    }
};
