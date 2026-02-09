<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id()->comment('ID призу');
            $table->foreignId('draw_id')->constrained()->onDelete('cascade')->comment('Зв’язок з розіграшем');
            $table->string('title')->comment('Назва призу (напр. Набір наклейок)');
            $table->integer('quantity')->default(1)->comment('Кількість таких призів');
            $table->integer('sort_order')->default(0)->comment('Порядок розігрування (черговість)');

            $table->foreignId('winner_participant_id')
                  ->nullable()
                  ->constrained('participants')
                  ->onDelete('set null')
                  ->comment('ID переможця з таблиці учасників');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prizes');
    }
};
