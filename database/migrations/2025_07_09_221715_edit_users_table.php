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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('club_entry_date');
            $table->dropColumn('profile_photo_path');

            $table->text('why_tt')->nullable()->after('occupation_description');
            $table->string('mail_address')->nullable()->after('why_tt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('why_tt');
            $table->dropColumn('mail_address');

            $table->date('club_entry_date')->nullable()->after('birth_date');
            $table->string('profile_photo_path', 2048)->nullable()->after('password');
        });
    }
};
