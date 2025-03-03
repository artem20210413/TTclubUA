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
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('vin_code');
            $table->string('vin_code', 64)->nullable()->after('name');

            $table->dropColumn('license_plate');
            $table->string('license_plate', 15)->unique()->nullable()->after('vin_code');

            $table->unsignedBigInteger('color_id')->nullable()->after('model_id');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['color_id']); // Удаляем внешний ключ
            $table->dropColumn('color_id');

            $table->dropColumn('license_plate');
            $table->string('license_plate', 15)->unique()->after('vin_code');

            $table->dropColumn('vin_code');
            $table->string('vin_code', 64)->nullable()->unique()->after('name');

        });
    }
};
