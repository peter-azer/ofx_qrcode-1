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
        Schema::create('qrcodes', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('user_id'); // Foreign key for users
            $table->unsignedBigInteger('profile_id'); // Foreign key for profiles
            $table->string('qrcode'); // Field for storing the QR code
            $table->string('link'); // Field for storing the associated link
            $table->unsignedInteger('scan_count')->default(0);
            $table->enum('type', ['link', 'whatsapp', 'wifi','pdf','events']); // Field for counting scans
            $table->boolean('is_active')->default(true); // Field for active status
            $table->unsignedBigInteger('package_id'); // Foreign key for packages
            $table->timestamps(); // Creates created_at and updated_at fields

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qrcodes');
    }
};
