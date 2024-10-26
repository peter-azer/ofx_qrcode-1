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
        Schema::create('wifi', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('qrcode_id')->nullable(); // Foreign key for profiles
            $table->string('name'); // Field for storing the WiFi name
            $table->string('password')->nullable(); // Field for storing the WiFi password
            $table->enum('encryption', ['WPA', 'WEP', 'None'])->default('WPA'); // Field for storing encryption type
            // Creates created_at and updated_at fields

            // Foreign key constraint
            $table->foreign('qrcode_id')->references('id')->on('qrcodes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi');
    }
};
