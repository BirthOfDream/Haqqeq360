<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bootcamp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BootcampSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users who could be instructors
        // You might want to filter by role if you have roles implemented
        $instructors = User::inRandomOrder()->limit(5)->pluck('id')->toArray();
        
        if (empty($instructors)) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        $bootcamps = [
            [
                'title' => 'Full Stack Web Development Bootcamp',
                'slug' => 'full-stack-web-development-bootcamp',
                'special' => true,
                'description' => 'Master both frontend and backend development with React, Node.js, and PostgreSQL. Build 5 real-world projects and launch your career as a full-stack developer.',
                'duration_weeks' => 12,
                'level' => 'intermediate',
                'start_date' => now()->addWeeks(2)->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 30,
                'certificate' => true,
                'cover_image' => 'bootcamps/fullstack-web-dev.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Data Science & Machine Learning',
                'slug' => 'data-science-machine-learning',
                'special' => true,
                'description' => 'Learn Python, pandas, scikit-learn, and TensorFlow. Work on real datasets and build predictive models from scratch.',
                'duration_weeks' => 16,
                'level' => 'advanced',
                'start_date' => now()->addWeeks(3)->format('Y-m-d'),
                'mode' => 'hybrid',
                'seats' => 25,
                'certificate' => true,
                'cover_image' => 'bootcamps/data-science-ml.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Mobile App Development with Flutter',
                'slug' => 'mobile-app-development-flutter',
                'special' => false,
                'description' => 'Build cross-platform mobile apps for iOS and Android using Flutter and Dart. Deploy your apps to both app stores.',
                'duration_weeks' => 10,
                'level' => 'beginner',
                'start_date' => now()->addWeeks(1)->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 40,
                'certificate' => true,
                'cover_image' => 'bootcamps/flutter-mobile.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'DevOps & Cloud Engineering',
                'slug' => 'devops-cloud-engineering',
                'special' => false,
                'description' => 'Master Docker, Kubernetes, AWS, and CI/CD pipelines. Learn infrastructure as code with Terraform.',
                'duration_weeks' => 14,
                'level' => 'intermediate',
                'start_date' => now()->addMonth()->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 20,
                'certificate' => true,
                'cover_image' => 'bootcamps/devops-cloud.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'UI/UX Design Fundamentals',
                'slug' => 'ui-ux-design-fundamentals',
                'special' => false,
                'description' => 'Learn design thinking, user research, wireframing, and prototyping. Master Figma and Adobe XD.',
                'duration_weeks' => 8,
                'level' => 'beginner',
                'start_date' => now()->addWeeks(2)->format('Y-m-d'),
                'mode' => 'hybrid',
                'seats' => 35,
                'certificate' => false,
                'cover_image' => 'bootcamps/ui-ux-design.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Cybersecurity Essentials',
                'slug' => 'cybersecurity-essentials',
                'special' => true,
                'description' => 'Learn ethical hacking, penetration testing, and security best practices. Prepare for CompTIA Security+ certification.',
                'duration_weeks' => 12,
                'level' => 'intermediate',
                'start_date' => now()->addWeeks(4)->format('Y-m-d'),
                'mode' => 'offline',
                'seats' => 15,
                'certificate' => true,
                'cover_image' => 'bootcamps/cybersecurity.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Digital Marketing & SEO',
                'slug' => 'digital-marketing-seo',
                'special' => false,
                'description' => 'Master social media marketing, Google Ads, content marketing, and search engine optimization strategies.',
                'duration_weeks' => 6,
                'level' => 'beginner',
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 50,
                'certificate' => false,
                'cover_image' => 'bootcamps/digital-marketing.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Blockchain & Web3 Development',
                'slug' => 'blockchain-web3-development',
                'special' => true,
                'description' => 'Build decentralized applications (dApps) with Solidity, Ethereum, and smart contracts. Explore NFTs and DeFi.',
                'duration_weeks' => 10,
                'level' => 'advanced',
                'start_date' => now()->addWeeks(5)->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 20,
                'certificate' => true,
                'cover_image' => 'bootcamps/blockchain-web3.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Game Development with Unity',
                'slug' => 'game-development-unity',
                'special' => false,
                'description' => 'Create 2D and 3D games using Unity and C#. Learn game design, physics, and monetization strategies.',
                'duration_weeks' => 12,
                'level' => 'intermediate',
                'start_date' => now()->addWeeks(3)->format('Y-m-d'),
                'mode' => 'hybrid',
                'seats' => 25,
                'certificate' => true,
                'cover_image' => 'bootcamps/game-dev-unity.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
            [
                'title' => 'Introduction to Programming',
                'slug' => 'introduction-to-programming',
                'special' => false,
                'description' => 'Perfect for absolute beginners. Learn programming fundamentals with Python and build your first applications.',
                'duration_weeks' => 8,
                'level' => 'beginner',
                'start_date' => now()->addWeek()->format('Y-m-d'),
                'mode' => 'online',
                'seats' => 60,
                'certificate' => false,
                'cover_image' => 'bootcamps/intro-programming.jpg',
                'instructor_id' => $instructors[array_rand($instructors)],
            ],
        ];

        DB::table('bootcamps')->insert(
            array_map(function ($bootcamp) {
                return array_merge($bootcamp, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, $bootcamps)
        );

        $this->command->info('Bootcamps seeded successfully!');
    }
}