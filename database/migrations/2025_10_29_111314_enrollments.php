<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('enrollable'); // Creates enrollable_id and enrollable_type
            $table->decimal('progress', 5, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'enrollable_id', 'enrollable_type'], 'unique_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }

};
