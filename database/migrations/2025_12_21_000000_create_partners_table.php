<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // Соціальні мережі та посилання
            $table->string('website_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('google_maps_url')->nullable();

            // Налаштування відображення
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            // Дати
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
