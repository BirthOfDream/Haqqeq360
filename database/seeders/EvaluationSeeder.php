<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $evaluation = new Evaluation();
        
        // Example: Create evaluation for a course
        $courseEvaluation = Evaluation::create([
            'product_type' => 'course',
            'product_id' => 1, // Assuming course with ID 1 exists
            'product_name' => 'Laravel Development Course',
            'is_active' => true,
        ]);

        // Load standard questions
        $standardQuestions = $evaluation->loadStandardQuestions();
        
        foreach ($standardQuestions as $question) {
            $courseEvaluation->questions()->create($question);
        }

        // Example: Create evaluation for a bootcamp
        $bootcampEvaluation = Evaluation::create([
            'product_type' => 'bootcamp',
            'product_id' => 1, // Assuming bootcamp with ID 1 exists
            'product_name' => 'Full Stack Development Bootcamp',
            'is_active' => true,
        ]);

        foreach ($standardQuestions as $question) {
            $bootcampEvaluation->questions()->create($question);
        }

        $this->command->info('Evaluations seeded successfully!');
    }
}