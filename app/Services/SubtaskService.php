<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class SubtaskService
{
    /**
     * Add a new subtask to a task
     *
     * @param int $taskId
     * @param string $title
     * @param string|null $description
     * @return Task
     */
    public function store(int $taskId, string $title, ?string $description = null): Task
    {
        // Get the task
        $task = Task::findOrFail($taskId);
        
        // Add the subtask
        $task->addSubtask($title, $description);
        
        return $task;
    }

    /**
     * Toggle the completion status of a subtask
     *
     * @param int $taskId
     * @param int $subtaskId
     * @return Task
     */
    public function toggle(int $taskId, int $subtaskId): Task
    {
        // Find the task
        $task = Task::findOrFail($taskId);
        
        // Find the subtask within the task's subtasks
        $subtask = collect($task->subtasks)->firstWhere('id', $subtaskId);
        
        if (!$subtask) {
            abort(404, 'Subtask not found.');
        }

        // Toggle the status (pass the opposite of current status)
        $task->updateSubtaskStatus($subtaskId, !$subtask['completed']);
        
        return $task;
    }

    /**
     * Remove a subtask from a task
     *
     * @param int $taskId
     * @param int $subtaskId
     * @return Task
     */
    public function destroy(int $taskId, int $subtaskId): Task
    {
        // Find the task
        $task = Task::findOrFail($taskId);
        
        // Find the subtask within the task's subtasks
        $subtask = collect($task->subtasks)->firstWhere('id', $subtaskId);
        
        if (!$subtask) {
            abort(404, 'Subtask not found.');
        }

        // Remove the subtask
        $task->removeSubtask($subtaskId);
        
        return $task;
    }
} 