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
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название категории
            $table->string('slug')->unique(); // ЧПУ-версия названия
            $table->text('description')->nullable(); // Описание категории
            $table->boolean('is_active')->default(true); // Активна ли категория
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->string('meta_title')->nullable(); // SEO-заголовок
            $table->text('meta_description')->nullable(); // SEO-описание
            $table->timestamps();
            $table->softDeletes(); // Мягкое удаление
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_categories');
    }
};
