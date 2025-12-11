<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('case_studies', function (Blueprint $table) {
    $table->foreignId('course_id')->constrained()->cascadeOnDelete();
    $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->string('content');
    $table->integer('duration')->default(60);
    $table->string('status')->default('draft');
    $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
    $table->text('guidelines')->nullable();
    $table->string('attachment')->nullable();
    $table->integer('max_score')->default(100);
    $table->integer('passing_score')->default(50);
    $table->integer('max_attempts')->default(1);
    $table->boolean('allow_late_submission')->default(false);
    $table->boolean('show_model_answer')->default(false);
    $table->boolean('peer_review_enabled')->default(false);
    $table->text('model_answer')->nullable();
    $table->timestamp('available_from')->nullable();
    $table->timestamp('available_until')->nullable();
});
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['lesson_id']);
            $table->dropColumn([
                'unit_id',
                'lesson_id',
                'guidelines',
                'attachment',
                'max_score',
                'passing_score',
                'max_attempts',
                'allow_late_submission',
                'show_model_answer',
                'peer_review_enabled',
                'model_answer',
                'available_from',
                'available_until',
            ]);
        });
    }
};