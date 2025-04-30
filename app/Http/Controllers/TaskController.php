<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Requests\TaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->middleware('auth');
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        $data = $this->taskService->index($request);
        
        return Inertia::render('Tasks/Index', $data);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return Inertia::render('Tasks/Create');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(TaskRequest $request)
    {
        $this->taskService->store($request->validated(), $request);
        
        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        if (Gate::denies('view', $task)) {
            abort(403);
        }
        
        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        return Inertia::render('Tasks/Edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $this->taskService->update($request->validated(), $request, $task);
        
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage (soft delete).
     */
    public function destroy(Task $task)
    {
        if (Gate::denies('delete', $task)) {
            abort(403);
        }
        
        $this->taskService->softDelete($task);
        
        return redirect()->route('tasks.index')->with('success', 'Task moved to trash.');
    }
    
    /**
     * Display a listing of the trashed tasks.
     */
    public function trashed(Request $request)
    {
        $data = $this->taskService->getTrashed($request);
        
        return Inertia::render('Tasks/Trashed', $data);
    }
    
    /**
     * Restore the specified task from trash.
     */
    public function restore($id)
    {
        $task = $this->taskService->restore($id);
        
        if (!$task) {
            return redirect()->route('tasks.trashed')->with('error', 'Task not found or not authorized.');
        }
        
        return redirect()->route('tasks.trashed')->with('success', 'Task restored successfully.');
    }
    
    /**
     * Permanently delete the specified task from storage.
     */
    public function forceDelete($id)
    {
        $result = $this->taskService->forceDelete($id);
        
        if (!$result) {
            return redirect()->route('tasks.trashed')->with('error', 'Task not found or not authorized.');
        }
        
        return redirect()->route('tasks.trashed')->with('success', 'Task permanently deleted.');
    }
    
    /**
     * Update the task status.
     */
    public function updateStatus(Request $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'status' => 'required|in:to-do,in-progress,done',
        ]);
        
        $this->taskService->updateStatus($validated, $task);
        
        return redirect()->back()->with('success', 'Task status updated successfully.');
    }
    
    /**
     * Toggle task publication status.
     */
    public function togglePublished(Request $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $this->taskService->togglePublished($task);
        
        return redirect()->back()->with('success', 'Task publication status updated.');
    }
    
    /**
     * Add a subtask to a task.
     */
    public function addSubtask(Request $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string',
        ]);
        
        $this->taskService->addSubtask(
            $task, 
            $validated['title'],
            $validated['description'] ?? null
        );
        
        return redirect()->back()->with('success', 'Subtask added successfully.');
    }
    
    /**
     * Update a subtask's completion status.
     */
    public function updateSubtaskStatus(Request $request, Task $task, int $subtaskId)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'completed' => 'required|boolean',
        ]);
        
        $this->taskService->updateSubtaskStatus(
            $task,
            $subtaskId,
            $validated['completed']
        );
        
        return redirect()->back()->with('success', 'Subtask status updated successfully.');
    }
    
    /**
     * Remove a subtask from a task.
     */
    public function removeSubtask(Request $request, Task $task, int $subtaskId)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }
        
        $this->taskService->removeSubtask($task, $subtaskId);
        
        return redirect()->back()->with('success', 'Subtask removed successfully.');
    }
} 