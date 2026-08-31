<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTask extends Model
{
    protected $fillable = [
        'branch_id', 'category_id', 'title', 'description',
        'assigned_to', 'created_by', 'priority', 'status', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkTaskCategory::class, 'category_id');
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkTaskFollowup::class, 'task_id')->orderByDesc('created_at');
    }

    public function subtasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkTaskSubtask::class, 'work_task_id')->orderBy('sort_order')->orderBy('id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && !in_array($this->status, ['Completed', 'Cancelled'])
            && $this->due_date->isPast();
    }
}
