<?php

namespace Tests\Unit\Commands;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupTrashedTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_old_trashed_tasks(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create 3 tasks: one trashed 31 days ago, one 29 days ago, and one not trashed
        $oldTask = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Trashed Task',
        ]);
        $oldTask->delete();
        // Manually update the deleted_at timestamp to be 31 days ago
        Task::withTrashed()->where('id', $oldTask->id)->update([
            'deleted_at' => Carbon::now()->subDays(31)
        ]);
        
        $recentTask = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Recent Trashed Task',
        ]);
        $recentTask->delete();
        // Manually update the deleted_at timestamp to be 29 days ago
        Task::withTrashed()->where('id', $recentTask->id)->update([
            'deleted_at' => Carbon::now()->subDays(29)
        ]);
        
        $activeTask = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Active Task',
        ]);
        
        // Run the command
        $this->artisan('tasks:cleanup-trashed')
            ->expectsOutput('Looking for tasks trashed more than 30 days ago...')
            ->expectsOutput('Found 1 trashed tasks to clean up.')
            ->expectsOutputToContain("Permanently deleted: ID: {$oldTask->id}, Title: Old Trashed Task")
            ->expectsOutput('Successfully cleaned up 1 trashed tasks.')
            ->assertExitCode(0);
        
        // Assert the old task is gone
        $this->assertDatabaseMissing('tasks', [
            'id' => $oldTask->id,
        ]);
        
        // Assert the recent task is still in trash
        $this->assertSoftDeleted('tasks', [
            'id' => $recentTask->id,
        ]);
        
        // Assert the active task is still active
        $this->assertDatabaseHas('tasks', [
            'id' => $activeTask->id,
            'deleted_at' => null,
        ]);
    }

    public function test_command_accepts_custom_days_parameter(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a task and trash it 10 days ago
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Task Trashed 10 Days Ago',
        ]);
        $task->delete();
        // Manually update the deleted_at timestamp
        Task::withTrashed()->where('id', $task->id)->update([
            'deleted_at' => Carbon::now()->subDays(10)
        ]);
        
        // Run the command with custom days parameter (5 days)
        $this->artisan('tasks:cleanup-trashed --days=5')
            ->expectsOutput('Looking for tasks trashed more than 5 days ago...')
            ->expectsOutput('Found 1 trashed tasks to clean up.')
            ->expectsOutputToContain("Permanently deleted: ID: {$task->id}, Title: Task Trashed 10 Days Ago")
            ->expectsOutput('Successfully cleaned up 1 trashed tasks.')
            ->assertExitCode(0);
        
        // Assert the task is permanently deleted
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_command_outputs_message_when_no_tasks_found(): void
    {
        // Run the command (there should be no tasks in the database yet)
        $this->artisan('tasks:cleanup-trashed')
            ->expectsOutput('Looking for tasks trashed more than 30 days ago...')
            ->expectsOutput('No trashed tasks found to clean up.')
            ->assertExitCode(0);
    }
} 