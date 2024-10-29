<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->timestamp('approve_verified_at')->nullable()->after('phone_verified_at');
            $table->tinyInteger('active')->default(1)->after('approve_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'phone_verified_at', 'approve_verified_at', 'active']);
        });
    }
};
