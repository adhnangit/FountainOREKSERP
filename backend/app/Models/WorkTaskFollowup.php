<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTaskFollowup extends Model
{
    protected $fillable = ['task_id', 'user_id', 'note', 'status_snapshot'];

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'task_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
