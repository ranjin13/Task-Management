<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'image_path',
        'is_published',
        'subtasks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'subtasks' => 'json',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'subtasks_count',
        'completed_subtasks_count',
        'subtasks_progress',
        'image_url',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the number of subtasks.
     */
    public function getSubtasksCountAttribute(): int
    {
        if (!$this->subtasks) {
            return 0;
        }
        
        return count($this->subtasks);
    }

    /**
     * Get the number of completed subtasks.
     */
    public function getCompletedSubtasksCountAttribute(): int
    {
        if (!$this->subtasks) {
            return 0;
        }
        
        return collect($this->subtasks)->where('completed', true)->count();
    }

    /**
     * Get the progress of completed subtasks (0-100).
     */
    public function getSubtasksProgressAttribute(): int
    {
        if ($this->subtasks_count === 0) {
            return 0;
        }
        
        return (int) round(($this->completed_subtasks_count / $this->subtasks_count) * 100);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Get the app URL from config
        $appUrl = config('app.url');
        
        // Check if the image path already has a full URL
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        
        // If path already contains 'storage', don't add it again
        if (str_contains($this->image_path, 'storage/')) {
            return $appUrl . '/' . $this->image_path;
        }
        
        // Standard case - return the full URL to the image
        return $appUrl . '/storage/' . $this->image_path;
    }

    /**
     * Add a subtask to the task.
     */
    public function addSubtask(string $title, string $description): self
    {
        $subtasks = $this->subtasks ?? [];
        
        $subtasks[] = [
            'id' => count($subtasks) + 1,
            'title' => $title,
            'description' => $description,
            'completed' => false,
            'created_at' => now()->toDateTimeString(),
        ];
        
        $this->subtasks = $subtasks;
        $this->save();
        
        return $this;
    }

    /**
     * Update a subtask's completion status.
     */
    public function updateSubtaskStatus(int $subtaskId, bool $completed): self
    {
        if (!$this->subtasks) {
            return $this;
        }
        
        $subtasks = collect($this->subtasks)->map(function ($subtask) use ($subtaskId, $completed) {
            if ($subtask['id'] === $subtaskId) {
                $subtask['completed'] = $completed;
                $subtask['updated_at'] = now()->toDateTimeString();
            }
            
            return $subtask;
        })->toArray();
        
        $this->subtasks = $subtasks;
        
        // Check if all subtasks are completed and update task status
        if ($this->getCompletedSubtasksCountAttribute() === $this->getSubtasksCountAttribute() && 
            $this->getSubtasksCountAttribute() > 0) {
            $this->status = 'done';
        }
        
        $this->save();
        
        return $this;
    }

    /**
     * Remove a subtask from the task.
     */
    public function removeSubtask(int $subtaskId): self
    {
        if (!$this->subtasks) {
            return $this;
        }
        
        $subtasks = collect($this->subtasks)->filter(function ($subtask) use ($subtaskId) {
            return $subtask['id'] !== $subtaskId;
        })->values()->toArray();
        
        $this->subtasks = $subtasks;
        $this->save();
        
        return $this;
    }
} 