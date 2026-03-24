<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_cars', function (Blueprint $table) {
            $table->id();

            // ID на RIA (для updateOrCreate)
            $table->unsignedBigInteger('external_id')->unique()->index();

            // Основні поля
            $table->string('plate_number')->nullable()->index();
            $table->string('vin')->nullable()->index();
            $table->string('title')->nullable();
            $table->decimal('price_usd', 12, 2)->nullable()->index();
            $table->string('city_name')->nullable();

            // Характеристики
            $table->string('mark_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->integer('year')->nullable();

            // Статус (активність та продаж)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sold')->default(false);

            // Зв'язок з юзером
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // JSON для всього іншого (фото, опис тощо)
            $table->json('raw_data')->nullable();

            // created_at та updated_at вашої бази
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_cars');
    }
};
