<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure there are instructors
        $instructors = User::where('role', 'instructor')->pluck('id')->toArray();

        if(empty($instructors)) {
            $this->command->info("No instructors found. Please create some users with role 'instructor'.");
            return;
        }

        // Create 20 courses
        foreach(range(1, 20) as $i) {
            $title = "Course $i";
            Course::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => "This is a detailed description for $title.",
                'duration_weeks' => rand(2, 12),
                'level' => ['beginner','intermediate','advanced'][rand(0,2)],
                'mode' => ['online','offline','hybrid'][rand(0,2)],
                'cover_image' => "https://picsum.photos/seed/course$i/400/200",
                'status' => 'published',
                'instructor_id' => $instructors[array_rand($instructors)],
            ]);
        }
    }
}
