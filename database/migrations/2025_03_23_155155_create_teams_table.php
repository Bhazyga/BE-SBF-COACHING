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
        Schema::create('Teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('instalink');
            $table->string('facebooklink');
            $table->string('title');
            $table->string('gambar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('Teams');
    }
};
