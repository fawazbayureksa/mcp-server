<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::create($user);
        }

        // Create sample customers
        $customers = [
            [
                'name' => 'Alice Cooper',
                'email' => 'alice@customer.com',
                'phone' => '+1234567890',
            ],
            [
                'name' => 'Charlie Brown',
                'email' => 'charlie@customer.com',
                'phone' => '+0987654321',
            ],
        ];

        foreach ($customers as $customer) {
            \App\Models\Customer::create($customer);
        }

        // Create sample tasks
        $tasks = [
            [
                'title' => 'Complete project proposal',
                'description' => 'Write and submit the Q4 project proposal',
                'status' => 'pending',
                'user_id' => 1,
            ],
            [
                'title' => 'Review code changes',
                'description' => 'Review pull request #123',
                'status' => 'in_progress',
                'user_id' => 2,
            ],
            [
                'title' => 'Update documentation',
                'description' => 'Update API documentation for v2.0',
                'status' => 'completed',
                'user_id' => 1,
            ],
        ];

        foreach ($tasks as $task) {
            \App\Models\Task::create($task);
        }
    }
}
