<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected $taskService;
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->taskService = new TaskService();
    }
    
    public function test_soft_delete_method_deletes_task(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Service Task',
        ]);
        
        // Call the softDelete method
        $result = $this->taskService->softDelete($task);
        
        // Assert the method returns true
        $this->assertTrue($result);
        
        // Assert the task is soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    
    public function test_restore_method_restores_task(): void
    {
        // Create a task and soft delete it
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Restore Task',
        ]);
        $task->delete();
        
        // Call the restore method
        $restoredTask = $this->taskService->restore($task->id);
        
        // Assert a task was returned
        $this->assertNotNull($restoredTask);
        $this->assertEquals($task->id, $restoredTask->id);
        
        // Assert the task is no longer soft deleted
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
    
    public function test_restore_method_returns_null_for_nonexistent_task(): void
    {
        // Call the restore method with a non-existent ID
        $result = $this->taskService->restore(999);
        
        // Assert null was returned
        $this->assertNull($result);
    }
    
    public function test_restore_method_doesnt_restore_other_users_task(): void
    {
        // Create another user
        $otherUser = User::factory()->create();
        
        // Create a task for the other user and soft delete it
        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other User Task',
        ]);
        $task->delete();
        
        // Call the restore method
        $result = $this->taskService->restore($task->id);
        
        // Assert null was returned
        $this->assertNull($result);
        
        // Assert the task is still soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    
    public function test_get_trashed_method_returns_trashed_tasks(): void
    {
        // Create and trash some tasks
        $task1 = Task::factory()->create(['user_id' => $this->user->id])->delete();
        $task2 = Task::factory()->create(['user_id' => $this->user->id])->delete();
        
        // Create a request
        $request = new Request();
        
        // Call the getTrashed method
        $result = $this->taskService->getTrashed($request);
        
        // Assert the result contains the trashed tasks
        $this->assertIsArray($result);
        $this->assertArrayHasKey('trashedTasks', $result);
        $this->assertEquals(2, count($result['trashedTasks']));
    }
    
    public function test_force_delete_method_permanently_deletes_task(): void
    {
        // Create a task and soft delete it
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Force Delete Service Task',
        ]);
        $task->delete();
        
        // Assert task is soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
        
        // Call the forceDelete method
        $result = $this->taskService->forceDelete($task->id);
        
        // Assert the method returns true
        $this->assertTrue($result);
        
        // Assert the task is completely gone
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
    
    public function test_force_delete_method_returns_false_for_nonexistent_task(): void
    {
        // Call the forceDelete method with a non-existent ID
        $result = $this->taskService->forceDelete(999);
        
        // Assert false was returned
        $this->assertFalse($result);
    }
    
    public function test_force_delete_method_wont_delete_other_users_task(): void
    {
        // Create another user
        $otherUser = User::factory()->create();
        
        // Create a task for the other user and soft delete it
        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other User Task for Force Delete',
        ]);
        $task->delete();
        
        // Call the forceDelete method
        $result = $this->taskService->forceDelete($task->id);
        
        // Assert false was returned
        $this->assertFalse($result);
        
        // Assert the task is still in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
} 