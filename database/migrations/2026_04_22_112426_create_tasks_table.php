<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['inbox', 'keep', 'delegate', 'drop', 'done'])->default('inbox');
            $table->enum('only_you_category', [
                'strategy', 'key_decisions', 'key_people', 'responsibility',
            ])->nullable();
            $table->enum('source', ['manual', 'email', 'slack', 'calendar', 'voice'])->default('manual');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('focused_block_at')->nullable();
            $table->string('ai_suggestion')->nullable();
            $table->float('ai_confidence')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
