<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('draws', function (Blueprint $table) {
            $table->id()->comment('Унікальний ідентифікатор розіграшу');
            $table->string('title')->comment('Назва розіграшу (напр. Новорічний Гів)');
            $table->text('description')->nullable()->comment('Опис умов та деталей проведення');

            $table->enum('status', ['planned', 'active', 'finished', 'cancelled'])
                  ->default('planned')
                  ->comment('Статус: planned — створений, очікує активації; active — триває реєстрація або процес вибору переможців; finished — завершений, всі призи розіграні; cancelled — скасований адміністратором');

            $table->boolean('allow_multiple_wins')
                  ->default(false)
                  ->comment('Чи може один учасник виграти кілька різних призів');

            $table->boolean('is_public')
                  ->default(true)
                  ->comment('Чи дозволена самостійна реєстрація учасників через сайт/бот');

            $table->timestamp('registration_until')
                  ->nullable()
                  ->comment('Дата та час, до якого відкрита реєстрація');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('draws');
    }
};
