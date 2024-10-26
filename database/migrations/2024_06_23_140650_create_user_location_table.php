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
        Schema::create('user_location', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('qrcode_id'); // Change to unsigned bigint
            $table->string('location')->nullable(); // Nullable location field
            $table->timestamps(); // Creates created_at and updated_at fields
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_location');
    }
};
