<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    protected $table = 'transfer_items';

    protected $fillable = [
        'transfer_id', 'product_id', 'batch_id', 'product_name', 'requested_quantity',
        'dispatched_quantity', 'received_quantity', 'unit_cost', 'batch_number',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'dispatched_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function transfer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InterBranchTransfer::class, 'transfer_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
