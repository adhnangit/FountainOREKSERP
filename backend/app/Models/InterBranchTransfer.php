<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterBranchTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inter_branch_transfers';

    protected $fillable = [
        'from_branch_id', 'to_branch_id', 'requested_by', 'approved_by',
        'dispatched_by', 'received_by', 'transfer_number', 'status',
        'request_date', 'dispatch_date', 'received_date', 'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'dispatch_date' => 'date',
        'received_date' => 'date',
    ];

    public function fromBranch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransferItem::class, 'transfer_id');
    }

    public function requestedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
