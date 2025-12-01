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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('chat_id');
            $table->enum('direction', ['in', 'out']); // in=от пользователя, out=от бота
            $table->text('text')->nullable();
            $table->json('raw')->nullable(); // полный апдейт, если нужно
            $table->timestamps();

            $table->index(['chat_id']);
//            $table->index(['user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
