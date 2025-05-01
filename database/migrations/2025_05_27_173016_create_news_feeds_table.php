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
            $table->string('slug')->unique(); // ЧПУ-ссылка для SEO
            $table->text('description'); // Краткое описание/анонс новости
            $table->longText('content')->nullable(); // Полное содержание новости
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Статусы новости
            $table->timestamp('published_at')->nullable(); // Дата и время публикации
            $table->timestamp('archived_at')->nullable(); // Дата и время архивации
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Автор новости
            $table->foreignId('category_id')->nullable()->constrained('news_categories')->onDelete('set null'); // Категория новости
            $table->string('image')->nullable(); // Главное изображение новости
            $table->integer('views_count')->default(0); // Счетчик просмотров
            $table->boolean('is_featured')->default(false); // Признак избранной новости
            $table->string('meta_title')->nullable(); // Мета-заголовок для SEO
            $table->text('meta_description')->nullable(); // Мета-описание для SEO
            $table->timestamps();
            $table->softDeletes(); // Мягкое удаление
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
