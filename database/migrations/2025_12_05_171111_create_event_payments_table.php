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
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('order_id')->unique();
            $table->integer('amount');
            $table->string('status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->string('snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
