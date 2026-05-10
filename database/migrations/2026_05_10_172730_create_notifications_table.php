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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // Зв'язок з користувачем
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Контент
            $table->string('title');
            $table->text('body');

            // Тип (використовуємо твій Enum)
            $table->string('type');

            // Параметри (JSON) - тут буде car_id, post_id тощо
            $table->json('data')->nullable();

            // Статус прочитання
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Індекси для швидкості
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
