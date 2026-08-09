<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_digests', function (Blueprint $table) {
            $table->unsignedInteger('total_messages')->default(0)->after('source_message_count');
            $table->json('top_active')->nullable()->after('total_messages'); // top 3 { "@user": count }
        });
    }

    public function down(): void
    {
        Schema::table('daily_digests', function (Blueprint $table) {
            $table->dropColumn(['total_messages', 'top_active']);
        });
    }
};
