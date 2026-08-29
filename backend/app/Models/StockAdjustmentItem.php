<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $table = 'stock_adjustment_items';

    protected $fillable = [
        'adjustment_id', 'product_id', 'batch_id', 'system_quantity', 'physical_quantity',
        'difference', 'unit_cost', 'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function adjustment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'adjustment_id');
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
