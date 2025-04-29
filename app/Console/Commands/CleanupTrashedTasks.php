<?php

namespace App\Console\Commands;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupTrashedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cleanup-trashed {--days=30 : Number of days after which to permanently delete trashed tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete tasks that have been in trash for more than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Looking for tasks trashed more than {$days} days ago...");

        $cutoffDate = Carbon::now()->subDays($days);
        
        $trashedTasks = Task::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->get();
            
        $count = $trashedTasks->count();
        
        if ($count === 0) {
            $this->info('No trashed tasks found to clean up.');
            return 0;
        }
        
        $this->info("Found {$count} trashed tasks to clean up.");
        
        foreach ($trashedTasks as $task) {
            // Store task info for logging
            $taskInfo = "ID: {$task->id}, Title: {$task->title}, Trashed on: {$task->deleted_at}";
            
            // Permanently delete the task
            $task->forceDelete();
            
            $this->line("Permanently deleted: {$taskInfo}");
        }
        
        $this->info("Successfully cleaned up {$count} trashed tasks.");
        
        return 0;
    }
}
