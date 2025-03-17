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
        Schema::create('tournament_baskets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id'); // Убедись, что это поле правильно указано
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->unsignedBigInteger('game_match_id');
            $table->foreign('game_match_id')->references('id')->on('game_matches')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('winner_team_id')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_baskets');
    }
};
