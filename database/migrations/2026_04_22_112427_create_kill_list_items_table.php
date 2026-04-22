<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kill_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('item_type', ['task', 'meeting', 'report', 'process', 'other'])->default('task');
            $table->string('title');
            $table->text('reason')->nullable();
            $table->boolean('was_necessary')->nullable();
            $table->boolean('served_clear_goal')->nullable();
            $table->boolean('anything_missing')->nullable();
            $table->timestamp('killed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kill_list_items');
    }
};
