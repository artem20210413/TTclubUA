<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id()->comment('ID запису учасника');
            $table->foreignId('draw_id')->constrained()->onDelete('cascade')->comment('Зв’язок з таблицею розіграшів');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('ID зареєстрованого юзера (якщо є)');

            $table->string('name_manual')->nullable()->comment('Ім’я для анонімних учасників');
            $table->string('contact_manual')->nullable()->comment('Контактні дані (TG/Email/Phone) для анонімів');

            $table->integer('weight')->default(1)->comment('Вага учасника (шанси): чим більше число, тим вища ймовірність виграшу');
            $table->boolean('is_winner')->default(false)->index()->comment('Чи виграв цей учасник вже щось у цьому розіграші');

            $table->timestamps();
            $table->index(['draw_id', 'is_winner']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('participants');
    }
};
