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
        Schema::create('mono_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique()->nullable(); // для проверки дублей и подтверждения
            $table->string('jar_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->unsignedInteger('currency_code')->nullable();
            $table->string('description')->nullable();
            $table->string('comment')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->json('payload')->nullable(); // можно хранить весь запрос от Monobank
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mono_transactions');
    }
};
