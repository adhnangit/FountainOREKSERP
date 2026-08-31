<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTaskSubtask extends Model
{
    protected $fillable = ['work_task_id', 'title', 'completed', 'assigned_to', 'due_date', 'sort_order'];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

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
