<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTrashTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_task_can_be_moved_to_trash(): void
    {
        // Act as the authenticated user
        $this->actingAs($this->user);
        
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'status' => 'to-do',
        ]);
        
        // Check the task exists
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Test Task',
        ]);
        
        // Send delete request to move to trash
        $response = $this->delete(route('tasks.destroy', $task->id));
        
        // Assert the request was successful and redirected
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
        
        // Assert the task is now soft deleted (not in regular results but in trashed)
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_task_can_be_restored_from_trash(): void
    {
        // Act as the authenticated user
        $this->actingAs($this->user);
        
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Task for Restore',
            'description' => 'This is a test task that will be restored',
            'status' => 'to-do',
        ]);
        
        // Soft delete the task
        $task->delete();
        
        // Check task is soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id
        ]);
        
        // Restore the task
        $response = $this->post(route('tasks.restore', $task->id));
        
        // Assert the request was successful and redirected
        $response->assertRedirect(route('tasks.trashed'));
        $response->assertSessionHas('success');
        
        // Assert the task is now visible in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }

    public function test_task_can_be_permanently_deleted(): void
    {
        // Act as the authenticated user
        $this->actingAs($this->user);
        
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Task for Permanent Delete',
            'description' => 'This is a test task that will be permanently deleted',
            'status' => 'to-do',
        ]);
        
        // Soft delete the task first
        $task->delete();
        
        // Check task is soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id
        ]);
        
        // Permanently delete the task
        $response = $this->delete(route('tasks.force-delete', $task->id));
        
        // Assert the request was successful and redirected
        $response->assertRedirect(route('tasks.trashed'));
        $response->assertSessionHas('success');
        
        // Assert the task is completely removed from the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_can_view_trashed_tasks(): void
    {
        // Act as the authenticated user
        $this->actingAs($this->user);
        
        // Create some tasks and trash them
        $task1 = Task::factory()->create(['user_id' => $this->user->id])->delete();
        $task2 = Task::factory()->create(['user_id' => $this->user->id])->delete();
        
        // Access the trashed tasks page
        $response = $this->get(route('tasks.trashed'));
        
        // Assert the request was successful
        $response->assertStatus(200);
        
        // Assert the view contains the trashed tasks component
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Trashed')
            ->has('trashedTasks.data', 2)
        );
    }

    public function test_user_cannot_access_others_trashed_tasks(): void
    {
        // Create another user
        $anotherUser = User::factory()->create();
        
        // Create a task for the other user and trash it
        $task = Task::factory()->create([
            'user_id' => $anotherUser->id,
            'title' => 'Other User Task',
        ]);
        $task->delete();
        
        // Act as our test user
        $this->actingAs($this->user);
        
        // Try to restore the other user's task
        $response = $this->post(route('tasks.restore', $task->id));
        
        // Should be redirected with an error
        $response->assertRedirect(route('tasks.trashed'));
        $response->assertSessionHas('error');
        
        // Task should still be in trash
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id
        ]);
    }
} 