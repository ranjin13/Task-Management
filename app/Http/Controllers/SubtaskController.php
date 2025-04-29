<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SubtaskController extends Controller
{
    /**
     * Store a newly created subtask in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify the task belongs to the authenticated user
        $task = Task::findOrFail($validated['task_id']);
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Add the subtask using the task model's method
        $task->addSubtask(
            $validated['title'],
            $validated['description'] ?? null
        );

        return Redirect::back()->with('success', 'Subtask created successfully.');
    }

    /**
     * Toggle the completion status of the specified subtask.
     *
     * @param  int  $subtaskId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(Request $request, $subtaskId)
    {
        // Get the task ID from the request
        $taskId = $request->input('task_id');
        if (!$taskId) {
            abort(400, 'Task ID is required.');
        }

        // Find the task and verify ownership
        $task = Task::findOrFail($taskId);
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Find the subtask within the task's subtasks
        $subtask = collect($task->subtasks)->firstWhere('id', (int) $subtaskId);
        if (!$subtask) {
            abort(404, 'Subtask not found.');
        }

        // Toggle the status (pass the opposite of current status)
        $task->updateSubtaskStatus((int) $subtaskId, !$subtask['completed']);

        return Redirect::back()->with('success', 'Subtask status updated.');
    }

    /**
     * Remove the specified subtask from storage.
     *
     * @param  int  $subtaskId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $subtaskId)
    {
        // Get the task ID from the request
        $taskId = $request->input('task_id');
        if (!$taskId) {
            abort(400, 'Task ID is required.');
        }

        // Find the task and verify ownership
        $task = Task::findOrFail($taskId);
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Find the subtask within the task's subtasks
        $subtask = collect($task->subtasks)->firstWhere('id', (int) $subtaskId);
        if (!$subtask) {
            abort(404, 'Subtask not found.');
        }

        // Remove the subtask
        $task->removeSubtask((int) $subtaskId);

        return Redirect::back()->with('success', 'Subtask deleted successfully.');
    }
} 