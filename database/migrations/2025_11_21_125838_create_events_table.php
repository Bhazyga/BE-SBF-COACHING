<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('category', ['webinar', 'training', 'english_club']);
            $table->string('speaker');
            $table->date('date');
            $table->string('time')->nullable();      // contoh: 19:00 WIB
            $table->string('duration')->nullable();  // contoh: "2 Jam"
            $table->string('platform')->nullable();  // Zoom / GMeet / Offline
            $table->string('thumbnail')->nullable();
            $table->longText('description');
            $table->boolean('is_paid')->default(false);
            $table->integer('price')->nullable();
            $table->string('whatsapp_group')->nullable();
            $table->string('extra_link')->nullable(); // link Zoom / lokasi
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
