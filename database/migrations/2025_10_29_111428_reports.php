<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('enrollable_type');
            $table->unsignedBigInteger('enrollable_id');
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('grade_avg', 5, 2)->nullable();
            $table->text('feedback_summary')->nullable();
            $table->timestamps();

            $table->index(['enrollable_type', 'enrollable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
