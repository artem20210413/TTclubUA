<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_digests', function (Blueprint $table) {
            $table->id();
            $table->date('digest_date')->unique(); // one digest per calendar day (idempotency)
            $table->string('status')->default('pending'); // pending|delivered|failed|greetings_only
            $table->text('message')->nullable();
            $table->unsignedInteger('source_message_count')->default(0);
            $table->unsignedInteger('birthday_user_count')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_digests');
    }
};
