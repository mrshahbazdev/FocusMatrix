<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('gemini'); // gemini | openai | anthropic
            $table->text('api_key_encrypted')->nullable();
            $table->string('model')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('calls_this_month')->default(0);
            $table->unsignedInteger('monthly_limit')->default(200);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamp('quota_reset_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
