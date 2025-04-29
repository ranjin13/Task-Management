<?php

namespace App\Console\Commands;

use Database\Seeders\TaskSeeder;
use Illuminate\Console\Command;

class SeedTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-tasks {--count=10 : Number of tasks per status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with tasks for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding tasks...');
        $this->call('db:seed', [
            '--class' => TaskSeeder::class,
        ]);
        $this->info('Tasks seeded successfully!');
        
        return Command::SUCCESS;
    }
} 