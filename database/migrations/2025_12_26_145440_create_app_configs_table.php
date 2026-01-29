<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_configs', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique(); // 'android' или 'ios'
            $table->string('latest_version');
            $table->string('min_version');
            $table->string('store_url');
            $table->text('release_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};
