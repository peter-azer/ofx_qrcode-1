<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->string('phone_number', 15); // Field for storing phone numbers
            $table->text('message'); // Field for storing messages
            $table->unsignedBigInteger('qr_code_id'); // Foreign key referencing qr_codes table


            // Foreign key constraint
            $table->foreign('qr_code_id') // Use qr_code_id here
                  ->references('id')->on('qrcodes')
                  ->onDelete('cascade'); // Delete messages when the corresponding QR code is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
