<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('week');
            $table->text('q1_others_could_do')->nullable();
            $table->text('q2_delegated_late')->nullable();
            $table->text('q3_to_omit_next_week')->nullable();
            $table->text('q4_focused_decisions')->nullable();
            $table->integer('focus_score')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'year', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_checks');
    }
};
