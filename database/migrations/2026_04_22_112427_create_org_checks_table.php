<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('week');
            $table->boolean('decides_what_clear')->nullable();
            $table->boolean('responsibilities_clear')->nullable();
            $table->boolean('reports_short')->nullable();
            $table->boolean('teams_small')->nullable();
            $table->text('notes')->nullable();
            $table->integer('health_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_checks');
    }
};
