<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTaskSubtask extends Model
{
    protected $fillable = ['work_task_id', 'title', 'priority', 'completed', 'status', 'assigned_to', 'due_date', 'sort_order'];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * `completed` is kept only for the withCount('subtasks_completed_count')
     * aggregate query — everywhere else uses `status`. Deriving it here
     * whenever status changes means it can never drift out of sync,
     * regardless of which code path (controller, seeder, tinker) wrote it.
     */
    protected static function booted(): void
    {
        static::saving(function (self $subtask) {
            if ($subtask->isDirty('status')) {
                $subtask->completed = $subtask->status === 'Completed';
            }
        });
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && !in_array($this->status, ['Completed', 'Cancelled'])
            && $this->due_date->isPast();
    }

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkTaskFollowup::class, 'subtask_id')->orderByDesc('created_at');
    }
}
