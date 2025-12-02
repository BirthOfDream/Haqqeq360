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
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type'); // rating, scale, yes_no, text, grade
            $table->json('options')->nullable(); // للخيارات المتعددة
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            
            $table->index(['evaluation_id', 'order']);
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
