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
        Schema::create('images', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('profile_id'); // Foreign key for profiles
            $table->string('image_path'); // Field for storing the image path
     // Creates created_at and updated_at fields

            // Foreign key constraint
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
