<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->onDelete('cascade');
            $table->string('promo_title');
            $table->text('promo_description')->nullable();
            $table->string('discount_value')->nullable();
            $table->text('promo_code')->nullable();

            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
