<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Hassan',
                'second_name' => 'Asklany',
                'email' => 'hassan@example.com',
                'phone' => '01000000001',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'bio' => 'Admin user for the platform.',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Ali',
                'second_name' => 'Khalid',
                'email' => 'ali@example.com',
                'phone' => '01000000002',
                'password' => Hash::make('password123'),
                'role' => 'instructor',
                'bio' => 'Instructor user for testing courses and bootcamps.',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Sara',
                'second_name' => 'Mohamed',
                'email' => 'sara@example.com',
                'phone' => '01000000003',
                'password' => Hash::make('password123'),
                'role' => 'learner',
                'bio' => 'Learner user for testing enrollments.',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
