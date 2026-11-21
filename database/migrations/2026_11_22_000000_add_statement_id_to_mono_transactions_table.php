<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mono_transactions', function (Blueprint $table) {
            // Monobank statement item id: the webhook may be redelivered, each operation must be processed once.
            $table->string('statement_id')->nullable()->unique()->after('hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mono_transactions', function (Blueprint $table) {
            $table->dropUnique(['statement_id']);
            $table->dropColumn('statement_id');
        });
    }
};
