<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'leave_request_id', 'branch_id', 'date', 'status', 'time_in', 'time_out',
        'work_hours', 'late_minutes', 'notes', 'marked_by', 'source',
    ];

    protected $casts = [
        'date' => 'date',
        'work_hours' => 'decimal:2',
        'late_minutes' => 'integer',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function markedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function leaveRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
