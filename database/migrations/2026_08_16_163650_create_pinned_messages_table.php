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
        Schema::create('pinned_messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('message_id');
            $table->dateTime('unpin_at')->nullable();
            $table->boolean('delete_after_unpin')->default(false);
            $table->timestamps();

            $table->unique(['chat_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_messages');
    }
};
