<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Debugbar\Facades\Debugbar;

class TaskService
{
    /**
     * Get tasks with filtering, sorting and pagination.
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        $query = Task::where('user_id', Auth::id());
        
        // Apply filters
        if ($request->filled('status') && in_array($request->status, ['to-do', 'in-progress', 'done'])) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        // Apply ordering
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        
        if (in_array($orderBy, ['title', 'created_at']) && in_array($orderDirection, ['asc', 'desc'])) {
            $query->orderBy($orderBy, $orderDirection);
        }
        
        // Apply pagination
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }
        
        Debugbar::info('Query executed:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        
        $tasks = $query->paginate($perPage)->withQueryString();
        
        
        // Make sure pagination metadata is explicitly included
        $tasksArray = $tasks->toArray();
        
        Debugbar::info('Raw tasks array:', ['tasksArray' => $tasksArray]);
        
        // Ensure the meta property exists with all pagination info
        if (!isset($tasksArray['meta']) || !is_array($tasksArray['meta'])) {
            $tasksArray['meta'] = [
                'current_page' => $tasks->currentPage(),
                'from' => $tasks->firstItem(),
                'last_page' => $tasks->lastPage(),
                'links' => $tasks->linkCollection()->toArray(),
                'path' => $tasks->path(),
                'per_page' => $tasks->perPage(),
                'to' => $tasks->lastItem(),
                'total' => $tasks->total(),
            ];
        }
        
        return [
            'tasks' => $tasksArray,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Store a new task.
     *
     * @param array $validatedData
     * @param Request $request
     * @return Task
     */
    public function store(array $validatedData, Request $request): Task
    {
        $task = new Task($validatedData);
        $task->user_id = Auth::id();
        $task->is_published = $request->boolean('is_published', false);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('task-images', 'public');
            $task->image_path = $path;
        }
        
        $task->save();
        
        return $task;
    }
    
    /**
     * Update an existing task.
     *
     * @param array $validatedData
     * @param Request $request
     * @param Task $task
     * @return Task
     */
    public function update(array $validatedData, Request $request, Task $task): Task
    {
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($task->image_path) {
                Storage::disk('public')->delete($task->image_path);
            }
            
            $path = $request->file('image')->store('task-images', 'public');
            $task->image_path = $path;
        }
        
        $task->fill($validatedData);
        $task->is_published = $request->boolean('is_published', false);
        $task->save();
        
        return $task;
    }
    
    /**
     * Delete a task.
     *
     * @param Task $task
     * @return bool
     */
    public function destroy(Task $task): bool
    {
        if ($task->image_path) {
            Storage::disk('public')->delete($task->image_path);
        }
        
        return $task->delete();
    }
    
    /**
     * Update a task's status.
     *
     * @param array $validatedData
     * @param Task $task
     * @return Task
     */
    public function updateStatus(array $validatedData, Task $task): Task
    {
        $task->update(['status' => $validatedData['status']]);
        
        return $task;
    }
    
    /**
     * Toggle a task's published status.
     *
     * @param Task $task
     * @return Task
     */
    public function togglePublished(Task $task): Task
    {
        $task->update(['is_published' => !$task->is_published]);
        
        return $task;
    }
    
    /**
     * Add a subtask to a task.
     *
     * @param Task $task
     * @param string $title
     * @param string|null $description
     * @return Task
     */
    public function addSubtask(Task $task, string $title, string $description = null): Task
    {
        return $task->addSubtask($title, $description);
    }
    
    /**
     * Update a subtask's completion status.
     *
     * @param Task $task
     * @param int $subtaskId
     * @param bool $completed
     * @return Task
     */
    public function updateSubtaskStatus(Task $task, int $subtaskId, bool $completed): Task
    {
        return $task->updateSubtaskStatus($subtaskId, $completed);
    }
    
    /**
     * Remove a subtask from a task.
     *
     * @param Task $task
     * @param int $subtaskId
     * @return Task
     */
    public function removeSubtask(Task $task, int $subtaskId): Task
    {
        return $task->removeSubtask($subtaskId);
    }
} 