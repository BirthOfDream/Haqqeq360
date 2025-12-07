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
Schema::create('units', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->integer('order')->default(0);

    // Polymorphic: course, bootcamp, workshop
    $table->morphs('unitable');  // unitable_id + unitable_type

    $table->timestamps();
    $table->softDeletes();
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
