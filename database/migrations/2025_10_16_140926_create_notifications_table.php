<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_notifications_table.php
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sent_at']);
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
