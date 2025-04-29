<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroySubtaskRequest;
use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\ToggleSubtaskRequest;
use App\Services\SubtaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use Illuminate\Support\Facades\Redirect;

class SubtaskController extends Controller
{
    protected $subtaskService;

    public function __construct(SubtaskService $subtaskService)
    {
        $this->subtaskService = $subtaskService;
    }

    public function store(StoreSubtaskRequest $request)
    {
        // Get the validated data
        $validated = $request->validated();
        
        // Verify the task belongs to the authenticated user
        $task = Task::findOrFail($validated['task_id']);
        
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Use the service to store the subtask
        $this->subtaskService->store(
            $validated['task_id'],
            $validated['title'],
            $validated['description'] ?? null
        );

        return Redirect::back()->with('success', 'Subtask created successfully.');
    }

    public function toggle(ToggleSubtaskRequest $request, $subtaskId)
    {
        // Get the validated data
        $validated = $request->validated();
        
        // Verify the task belongs to the authenticated user
        $task = Task::findOrFail($validated['task_id']);
        
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Use the service to toggle the subtask status
        $this->subtaskService->toggle($validated['task_id'], (int) $subtaskId);

        return Redirect::back()->with('success', 'Subtask status updated.');
    }

    public function destroy(DestroySubtaskRequest $request, $subtaskId)
    {
        // Get the validated data
        $validated = $request->validated();
        
        // Verify the task belongs to the authenticated user
        $task = Task::findOrFail($validated['task_id']);
        
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Use the service to destroy the subtask
        $this->subtaskService->destroy($validated['task_id'], (int) $subtaskId);

        return Redirect::back()->with('success', 'Subtask deleted successfully.');
    }
} 