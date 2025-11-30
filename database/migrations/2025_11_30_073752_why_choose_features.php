<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Why Choose Us Features (EPIC 3.2)
 * US-WHY-01: عرض عناصر المميزات
 * US-WHY-02: إمكانية الضغط على البطاقة
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('why_choose_features', function (Blueprint $table) {
            $table->id();
            
            // Feature Content (US-WHY-01, FR-WHY-01)
            $table->string('title'); // عنوان الميزة
            $table->text('description'); // وصف ≤ 30 كلمة (FR-WHY-03)
            $table->string('icon')->nullable(); // SVG/PNG (FR-WHY-02)
            $table->string('icon_type')->default('svg'); // svg, png, image
            
            // Navigation (US-WHY-02, FR-WHY-04)
            $table->string('link_url')->nullable(); // رابط صفحة التفاصيل
            $table->boolean('enable_hover')->default(true); // Hover Animation
            
            // Display Settings (FR-WHY-05)
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('why_choose_features');
    }
};