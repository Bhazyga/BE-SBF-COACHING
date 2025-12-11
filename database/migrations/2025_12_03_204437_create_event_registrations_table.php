<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])
                  ->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->boolean('extra_link_sent')->default(false);
            $table->boolean('joined_event')->default(false);
            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
