<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeChecklistTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'type', 'title', 'description', 'due_date',
        'status', 'completed_by', 'completed_at', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function completedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
