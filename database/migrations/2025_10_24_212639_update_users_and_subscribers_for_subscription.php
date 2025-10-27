<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus relasi subscriber_id karena redundan
            if (Schema::hasColumn('users', 'subscriber_id')) {
                $table->dropForeign(['subscriber_id']);
                $table->dropColumn('subscriber_id');
            }

            // Ganti default role jadi user biasa
            $table->string('role')->default('user')->change();

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'status']);

            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->enum('plan', ['monthly', 'yearly'])->default('monthly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('transaction_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('subscriber_id')->nullable();
            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('set null');
            $table->string('role')->default('subscriber')->change();

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');

            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'plan', 'start_date', 'end_date', 'transaction_id']);
        });
    }
};
