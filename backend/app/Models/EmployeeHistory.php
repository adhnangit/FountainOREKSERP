<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    use HasFactory;

    // Eloquent's default table-name guess would pluralize "history" to "histories",
    // but the migration created this table as the singular "employee_history".
    protected $table = 'employee_history';

    protected $fillable = ['employee_id', 'field_changed', 'old_value', 'new_value', 'effective_date', 'changed_by', 'notes'];

    protected $casts = ['effective_date' => 'date'];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
