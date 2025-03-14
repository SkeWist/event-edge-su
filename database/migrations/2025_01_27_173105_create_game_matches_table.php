<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade'); // Игра
            $table->foreignId('team_1_id')->constrained('teams')->onDelete('cascade'); // Первая команда
            $table->foreignId('team_2_id')->constrained('teams')->onDelete('cascade'); // Вторая команда
            $table->timestamp('match_date'); // Дата и время матча
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled'); // Статус матча
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('stage_id')->nullable()->constrained('stages');
            $table->string('result');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};
