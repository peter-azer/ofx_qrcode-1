<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('user_id'); // User ID
            $table->string('cover')->nullable(); // Cover image
            $table->string('logo')->nullable(); // Logo image
            $table->string('background_color')->nullable(); // Background color
            $table->string('font')->nullable(); // Font type
            // Uncomment the following line to enforce foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
