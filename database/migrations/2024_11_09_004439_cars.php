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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('gene_id');
            $table->unsignedBigInteger('model_id');
            $table->string('name')->nullable();
            $table->string('license_plate', 15)->unique();
            $table->string('personalized_license_plate', 20)->nullable()->unique();
            $table->string('photo_path', 2048)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('gene_id')->references('id')->on('car_genes')->onDelete('restrict');
            $table->foreign('model_id')->references('id')->on('car_models')->onDelete('restrict');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
