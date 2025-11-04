<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name_ar' => 'تطوير البرمجيات',
                'name_en' => 'Software Development',
                'slug' => 'software-development',
                'description_ar' => 'برامج ودورات تطوير البرمجيات.',
                'description_en' => 'Software development programs and courses.',
                'parent_id' => null,
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'تصميم المواقع',
                'name_en' => 'Web Design',
                'slug' => 'web-design',
                'description_ar' => 'دورات متخصصة في تصميم المواقع.',
                'description_en' => 'Courses focused on web design.',
                'parent_id' => null,
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'تطوير الويب الكامل',
                'name_en' => 'Full Web Development',
                'slug' => 'full-web-development',
                'description_ar' => 'مسار لتعلم تطوير الويب الكامل.',
                'description_en' => 'Path to learn full web development.',
                'parent_id' => null,
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
        
        $this->command->info('Categories seeded successfully!');
    }
}