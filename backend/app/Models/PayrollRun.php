<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'month', 'year', 'status', 'created_by', 'paid_at', 'paid_by', 'notes'];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payslips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
