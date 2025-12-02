<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgramsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get category IDs - make sure categories are seeded first
        $softwareDevCategoryId = DB::table('categories')
            ->where('slug', 'software-development')
            ->value('id');
            
        $webDesignCategoryId = DB::table('categories')
            ->where('slug', 'web-design')
            ->value('id');
            
        $fullWebDevCategoryId = DB::table('categories')
            ->where('slug', 'full-web-development')
            ->value('id');

        if (!$softwareDevCategoryId) {
            $this->command->error('Categories not found. Please seed categories first.');
            return;
        }

        $programs = [
            [
                'title_ar' => 'مقدمة في البرمجة',
                'title_en' => 'Introduction to Programming',
                'slug' => 'intro-to-programming',
                'description_ar' => 'دورة تعلم أساسيات البرمجة.',
                'description_en' => 'Learn the basics of programming.',
                'category_id' => $softwareDevCategoryId,
                'difficulty_level' => 'beginner',
                'delivery_mode' => 'online',
                'duration_weeks' => 4,
                'duration_days' => 28,
                'price' => 500.00,
                'currency' => 'SAR',
                'cover_image_url' => 'https://example.com/images/program1.jpg',
                'is_published' => true,
                'is_featured' => true,
                'max_participants' => 100,
                'current_enrollments' => 20,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'published_at' => $now,
            ],
            [
                'title_ar' => 'بوتكامب تطوير الويب',
                'title_en' => 'Web Development Bootcamp',
                'slug' => 'web-dev-bootcamp',
                'description_ar' => 'تعلم تطوير الويب من الصفر.',
                'description_en' => 'Learn web development from scratch.',
                'category_id' => $fullWebDevCategoryId,
                'difficulty_level' => 'intermediate',
                'delivery_mode' => 'blended',
                'duration_weeks' => 12,
                'duration_days' => 84,
                'price' => 3500.00,
                'currency' => 'SAR',
                'cover_image_url' => 'https://example.com/images/program2.jpg',
                'is_published' => true,
                'is_featured' => false,
                'max_participants' => 50,
                'current_enrollments' => 10,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'published_at' => $now,
            ],
            [
                'title_ar' => 'دورة قواعد البيانات',
                'title_en' => 'Database Course',
                'slug' => 'database-course',
                'description_ar' => 'تعلم أساسيات قواعد البيانات.',
                'description_en' => 'Learn database fundamentals.',
                'category_id' => $softwareDevCategoryId,
                'difficulty_level' => 'beginner',
                'delivery_mode' => 'in_person',
                'duration_weeks' => 6,
                'duration_days' => 42,
                'price' => 800.00,
                'currency' => 'SAR',
                'cover_image_url' => 'https://example.com/images/program3.jpg',
                'is_published' => false,
                'is_featured' => false,
                'max_participants' => 30,
                'current_enrollments' => 0,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'published_at' => null,
            ],
        ];

        DB::table('programs')->insert($programs);
        
        $this->command->info('Programs seeded successfully!');
    }
}