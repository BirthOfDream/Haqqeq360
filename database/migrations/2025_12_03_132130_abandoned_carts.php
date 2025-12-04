<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
               Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('last_step', 50); // checkout, payment, review
            $table->string('device', 20)->nullable(); // mobile, desktop, tablet
            $table->string('country', 2)->nullable();
            $table->timestamp('last_attempt_at');
            $table->timestamp('reminded_at')->nullable();
            $table->enum('reminder_method', ['email', 'sms', 'both'])->nullable();
            $table->enum('status', ['abandoned', 'reminded', 'converted', 'expired'])->default('abandoned');
            $table->unsignedTinyInteger('reminder_count')->default(0);
            $table->json('cart_data')->nullable(); // للحفظ الكامل لبيانات السلة
            $table->timestamps();
            
            $table->index(['status', 'last_attempt_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
