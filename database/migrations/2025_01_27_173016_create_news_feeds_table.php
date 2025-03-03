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
        Schema::create('news_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Заголовок новости
            $table->text('description'); // Содержание новости
            $table->string('status');
            $table->timestamp('published_at')->nullable(); // Дата и время публикации
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Пользователь, который опубликовал новость
            $table->timestamps(); // Время создания и обновления
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_feeds');
    }
};
