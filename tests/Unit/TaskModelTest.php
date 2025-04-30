<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_use_soft_deletes(): void
    {
        // Create a user and a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task',
        ]);
        
        // Get the task ID before deletion
        $taskId = $task->id;
        
        // Delete the task
        $task->delete();
        
        // Task should not be found in the regular query
        $this->assertNull(Task::find($taskId));
        
        // Task should be found with withTrashed()
        $trashedTask = Task::withTrashed()->find($taskId);
        $this->assertNotNull($trashedTask);
        $this->assertNotNull($trashedTask->deleted_at);
        
        // Task should be found with onlyTrashed()
        $onlyTrashedTask = Task::onlyTrashed()->find($taskId);
        $this->assertNotNull($onlyTrashedTask);
    }
    
    /**
     * Test that the Task model can be restored.
     */
    public function test_tasks_can_be_restored(): void
    {
        // Create a user and a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Restorable Task',
        ]);
        
        // Get the task ID and delete it
        $taskId = $task->id;
        $task->delete();
        
        // Verify task is trashed
        $this->assertNull(Task::find($taskId));
        
        // Restore the task
        $trashedTask = Task::withTrashed()->find($taskId);
        $trashedTask->restore();
        
        // Task should now be found in regular queries
        $restoredTask = Task::find($taskId);
        $this->assertNotNull($restoredTask);
        $this->assertNull($restoredTask->deleted_at);
    }
    
    /**
     * Test that the Task model can be force deleted.
     */
    public function test_tasks_can_be_force_deleted(): void
    {
        // Create a user and a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Force Delete Task',
        ]);
        
        // Get the task ID
        $taskId = $task->id;
        
        // Soft delete
        $task->delete();
        
        // Task should be in trashed but still in database
        $this->assertSoftDeleted('tasks', [
            'id' => $taskId
        ]);
        
        // Force delete
        $trashedTask = Task::withTrashed()->find($taskId);
        $trashedTask->forceDelete();
        
        // Task should be completely gone
        $this->assertNull(Task::withTrashed()->find($taskId));
    }
    
    /**
     * Test that the correct database columns are used for soft deletes.
     */
    public function test_soft_delete_uses_correct_column(): void
    {
        // Create a user and a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Column Test Task',
        ]);
        
        // Soft delete the task
        $task->delete();
        
        // Verify that deleted_at column is set
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
        
        // Directly query the database to check the deleted_at column
        $trashedTask = Task::withTrashed()->find($task->id);
        $this->assertNotNull($trashedTask->deleted_at);
        
        // Make sure it's using Carbon instance (confirms date column is used)
        $this->assertInstanceOf(\Carbon\Carbon::class, $trashedTask->deleted_at);
    }
} 