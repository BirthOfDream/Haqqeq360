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
         Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['evaluation_id', 'user_id']);
        });
        
        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('evaluation_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('evaluation_questions')->cascadeOnDelete();
            $table->text('answer_value');
            $table->timestamps();
            
            $table->index(['response_id', 'question_id']);
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
