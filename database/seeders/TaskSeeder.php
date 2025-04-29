<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user for testing
        $user = User::first() ?? User::factory()->create();
        
        // Create 10 'to-do' tasks with random publication status
        Task::factory()
            ->count(10)
            ->todo()
            ->for($user)
            ->create();
            
        // Create 10 'in-progress' tasks with random publication status
        Task::factory()
            ->count(10)
            ->inProgress()
            ->for($user)
            ->create();
            
        // Create 10 'done' tasks with random publication status
        Task::factory()
            ->count(10)
            ->done()
            ->for($user)
            ->create();
            
        // Log to console for confirmation
        $this->command->info('Created 30 tasks (10 for each status) for user ID: ' . $user->id);
    }
} 