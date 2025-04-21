<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tournament_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('request_type')->default('user'); // user, operator
            $table->string('image')->nullable();
            $table->json('teams')->nullable();
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('stage_id')->references('id')->on('stages');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tournament_requests');
    }
}; 