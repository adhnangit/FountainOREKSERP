<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'review_id', 'title', 'description', 'target_date',
        'status', 'progress_percent', 'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'progress_percent' => 'integer',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }
}
