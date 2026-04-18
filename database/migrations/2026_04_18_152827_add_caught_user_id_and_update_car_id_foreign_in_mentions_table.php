<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentions', function (Blueprint $table) {
            $table->dropForeign(['car_id']);

            $table->bigInteger('car_id')->unsigned()->nullable()->change();
            $table->json('car_snapshot')->nullable()->after('car_id');
            $table->foreignId('caught_user_id')->nullable()->after('owner_id')->constrained('users')->onDelete('cascade');

            // 4. Перестворюємо ключ для car_id з SET NULL
            $table->foreign('car_id')
                ->references('id')
                ->on('cars')
                ->onDelete('set null');
        });

        $cars = DB::table('cars')->get();

        foreach ($cars as $car) {
            DB::table('mentions')
                ->where('car_id', $car->id)
                ->update([
                    'caught_user_id' => $car->user_id,
                    // Конвертуємо всі дані машини в JSON
                    'car_snapshot'   => json_encode($car)
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('mentions', function (Blueprint $table) {
            $table->dropForeign(['caught_user_id']);
            $table->dropColumn('caught_user_id');
            $table->dropColumn('car_snapshot');

            $table->dropForeign(['car_id']);

            // Повертаємо як було: NOT NULL та CASCADE
            $table->bigInteger('car_id')->unsigned()->nullable(false)->change();
            $table->foreign('car_id')
                ->references('id')
                ->on('cars')
                ->onDelete('cascade');
        });
    }
};
