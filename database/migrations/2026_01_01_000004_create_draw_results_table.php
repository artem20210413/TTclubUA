<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('draw_results', function (Blueprint $table) {
            $table->id()->comment('ID запису в лозі');
            $table->foreignId('prize_id')->constrained()->onDelete('cascade')->comment('Який саме приз було розіграно');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade')->comment('Хто став переможцем у цьому раунді');

            $table->enum('status', ['confirmed', 'cancelled'])
                  ->default('confirmed')
                  ->comment('Статус результату: confirmed — підтверджено, cancelled — анульовано (переграно)');

            $table->timestamp('rolled_at')->useCurrent()->comment('Час проведення розіграшу (кидка кубика)');

            $table->index(['prize_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('draw_results');
    }
};
