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
        Schema::table('pinned_messages', function (Blueprint $table) {
            $table->boolean('delete_after_unpin')->default(false)->after('unpin_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinned_messages', function (Blueprint $table) {
            $table->dropColumn('delete_after_unpin');
        });
    }
};
