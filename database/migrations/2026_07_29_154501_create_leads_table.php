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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->text('context')->nullable();
            
            $table->enum('call_type', ['manual', 'auto'])->default('manual');
            $table->integer('call_delay_minutes')->nullable();
            
            $table->string('status')->default('new');
            $table->string('call_status')->default('pending');
            $table->string('elevenlabs_conversation_id')->nullable();
            $table->text('call_summary')->nullable();
            $table->string('recording_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
