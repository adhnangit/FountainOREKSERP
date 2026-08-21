<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'branch_id', 'batch_code', 'batch_number', 'grn_item_id',
        'source_type', 'reference_type', 'reference_id', 'cost_price', 'selling_price',
        'quantity_received', 'quantity_remaining', 'received_date', 'expiry_date',
        'created_by', 'notes',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function grnItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GrnItem::class);
    }

    public function movements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
