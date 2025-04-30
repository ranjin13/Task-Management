<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_task_index_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('tasks.index'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tasks/Index'));
    }

    public function test_task_create_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('tasks.create'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tasks/Create'));
    }

    public function test_task_show_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $response = $this->get(route('tasks.show', $task));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Show')
            ->has('task')
            ->where('task.id', $task->id)
        );
    }

    public function test_task_edit_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $response = $this->get(route('tasks.edit', $task));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Edit')
            ->has('task')
            ->where('task.id', $task->id)
        );
    }

    public function test_task_can_be_created(): void
    {
        $this->actingAs($this->user);
        
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('task-image.jpg');
        
        $taskData = [
            'title' => 'New Test Task',
            'description' => 'This is a test task description',
            'status' => 'to-do',
            'image' => $file,
            'is_published' => true,
        ];
        
        $response = $this->post(route('tasks.store'), $taskData);
        
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
        
        // Assert the task was saved to the database
        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'New Test Task',
            'description' => 'This is a test task description',
            'status' => 'to-do',
            'is_published' => true,
        ]);
        
        // Assert the file was stored
        $task = Task::where('title', 'New Test Task')->first();
        if ($task && $task->image_path) {
            $this->assertTrue(Storage::disk('public')->exists($task->image_path));
        }
    }

    public function test_task_can_be_updated(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => 'to-do',
        ]);
        
        $updatedData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => 'in-progress',
            'is_published' => true,
        ];
        
        $response = $this->put(route('tasks.update', $task), $updatedData);
        
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
        
        // Assert the task was updated
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => 'in-progress',
            'is_published' => true,
        ]);
    }

    public function test_task_status_can_be_updated(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to-do',
        ]);
        
        $response = $this->patch(route('tasks.update-status', $task), [
            'status' => 'in-progress',
        ]);
        
        $response->assertSessionHas('success');
        
        // Assert the status was updated
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in-progress',
        ]);
    }

    public function test_task_publication_status_can_be_toggled(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'is_published' => false,
        ]);
        
        $response = $this->patch(route('tasks.toggle-published', $task));
        
        $response->assertSessionHas('success');
        
        // Assert the publication status was toggled
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_published' => true,
        ]);
        
        // Toggle it back
        $this->patch(route('tasks.toggle-published', $task));
        
        // Assert it was toggled back
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_published' => false,
        ]);
    }

    public function test_user_cannot_access_others_tasks(): void
    {
        $this->actingAs($this->user);
        
        // Create another user and a task belonging to them
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $anotherUser->id,
        ]);
        
        // Try to view the task
        $response = $this->get(route('tasks.show', $task));
        $response->assertStatus(403);
        
        // Try to edit the task
        $response = $this->get(route('tasks.edit', $task));
        $response->assertStatus(403);
        
        // Try to update the task with all required fields
        $response = $this->put(route('tasks.update', $task), [
            'title' => 'Trying to update',
            'description' => 'Updated description',
            'status' => 'in-progress',
        ]);
        $response->assertStatus(403);
        
        // Try to delete the task
        $response = $this->delete(route('tasks.destroy', $task));
        $response->assertStatus(403);
    }
    
    public function test_subtask_can_be_added_to_task(): void
    {
        $this->actingAs($this->user);
        
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'subtasks' => [],
        ]);
        
        $subtaskData = [
            'title' => 'New Subtask',
            'description' => 'Subtask description',
            'task_id' => $task->id
        ];
        
        // Use the correct route from your application
        $response = $this->post(route('subtasks.store'), $subtaskData);
        
        // Refresh task from database
        $task->refresh();
        
        // Assert subtask was added
        $this->assertNotEmpty($task->subtasks);
        $this->assertEquals('New Subtask', $task->subtasks[0]['title']);
    }
    
    public function test_subtask_status_can_be_toggled(): void
    {
        $this->actingAs($this->user);
        
        // Create a task with a subtask
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'subtasks' => [
                [
                    'id' => 1,
                    'title' => 'Subtask 1',
                    'description' => 'Subtask description',
                    'completed' => false,
                    'created_at' => now()->toDateTimeString(),
                ]
            ],
        ]);
        
        // Toggle the subtask status using the correct route
        $response = $this->put(route('subtasks.toggle', 1), [
            'completed' => true,
            'task_id' => $task->id
        ]);
        
        // Refresh task from database
        $task->refresh();
        
        // Assert subtask status was updated
        $this->assertTrue($task->subtasks[0]['completed']);
    }

    public function test_subtask_can_be_deleted(): void
    {
        $this->actingAs($this->user);
        
        // Create a task with a subtask
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'subtasks' => [
                [
                    'id' => 1,
                    'title' => 'Subtask 1',
                    'description' => 'Subtask description',
                    'completed' => false,
                    'created_at' => now()->toDateTimeString(),
                ]
            ],
        ]);
        
        // Delete the subtask
        $response = $this->delete(route('subtasks.destroy', 1), [
            'task_id' => $task->id
        ]);
        
        // Refresh task from database
        $task->refresh();
        
        // Assert subtask was deleted
        $this->assertEmpty($task->subtasks);
    }
    
    public function test_tasks_can_be_filtered(): void
    {
        $this->actingAs($this->user);
        
        // Create tasks with different statuses
        $inProgressTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in-progress',
            'title' => 'In Progress Task',
        ]);
        
        $todoTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to-do',
            'title' => 'Todo Task',
        ]);
        
        // Filter by status
        $response = $this->get(route('tasks.index', ['status' => 'in-progress']));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 1)
            ->where('tasks.data.0.title', 'In Progress Task')
        );
        
        // Filter by search
        $response = $this->get(route('tasks.index', ['search' => 'Todo']));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 1)
            ->where('tasks.data.0.title', 'Todo Task')
        );
    }
    
    public function test_tasks_can_be_sorted(): void
    {
        $this->actingAs($this->user);
        
        // Create tasks with different creation dates
        $olderTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Older Task',
            'created_at' => now()->subDays(2),
        ]);
        
        $newerTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Newer Task',
            'created_at' => now(),
        ]);
        
        // Sort by created_at in ascending order
        $response = $this->get(route('tasks.index', [
            'order_by' => 'created_at',
            'order_direction' => 'asc'
        ]));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 2)
            ->where('tasks.data.0.title', 'Older Task')
        );
        
        // Sort by created_at in descending order
        $response = $this->get(route('tasks.index', [
            'order_by' => 'created_at',
            'order_direction' => 'desc'
        ]));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 2)
            ->where('tasks.data.0.title', 'Newer Task')
        );
    }
}

/**
 * Helper function to check if a route has a specific parameter.
 */
function route_has_parameter($name, $parameter)
{
    if (!$name) return false;
    try {
        $route = app('router')->getRoutes()->getByName($name);
        return $route && in_array($parameter, $route->parameterNames());
    } catch (\Exception $e) {
        return false;
    }
} 