<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_digests', function (Blueprint $table) {
            $table->longText('prompt')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('daily_digests', function (Blueprint $table) {
            $table->dropColumn('prompt');
        });
    }
};
