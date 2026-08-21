<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'grn_items';

    protected $fillable = [
        'grn_id', 'product_id', 'po_item_id', 'product_name', 'unit',
        'quantity_ordered', 'quantity_received', 'unit_cost', 'total_cost',
        'batch_number', 'expiry_date', 'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function grn(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'grn_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Batch::class);
    }
}
