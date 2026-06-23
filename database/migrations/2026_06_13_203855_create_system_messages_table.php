<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
   Schema::create('system_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_id')->constrained('chat_rooms')->cascadeOnDelete(); // 👈 غيرت rooms لـ chat_rooms
        $table->string('type');
        $table->text('content');
        $table->json('data')->nullable();
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('system_messages');
    }
};