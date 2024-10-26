<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdfs', function (Blueprint $table) {
            $table->id(); // Creates a bigint ID
            $table->unsignedBigInteger('qrcode_id')->nullable(); // Assuming it references UserLocation
            $table->string('path'); // Field for storing the PDF path



            $table->foreign('qrcode_id')->references('id')->on('qrcodes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdfs');
    }
}
