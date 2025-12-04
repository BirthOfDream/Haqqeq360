<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link Tree Page Settings
        Schema::create('link_tree_settings', function (Blueprint $table) {
            $table->id();
            $table->string('background_color')->default('#ffffff');
            $table->string('button_color')->default('#000000');
            $table->string('text_color')->default('#ffffff');
            $table->string('font_family')->default('Arial');
            $table->string('page_title')->nullable();
            $table->text('page_description')->nullable();
            $table->string('slug')->unique()->default('links');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Individual Links
        Schema::create('link_tree_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('icon')->nullable(); // Optional icon class
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('clicks')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('order');
            $table->index('is_active');
        });

        // Link Analytics
        Schema::create('link_tree_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('link_tree_links')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();

            $table->index('link_id');
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_tree_clicks');
        Schema::dropIfExists('link_tree_links');
        Schema::dropIfExists('link_tree_settings');
    }
};