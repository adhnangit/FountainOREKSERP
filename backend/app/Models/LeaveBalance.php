<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'leave_type_id', 'year', 'allocated_days', 'used_days', 'carried_forward'];

    protected $appends = ['remaining_days'];

    protected $casts = [
        'year' => 'integer',
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'carried_forward' => 'decimal:1',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return (float) $this->allocated_days + (float) $this->carried_forward - (float) $this->used_days;
    }
}
