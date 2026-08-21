<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBranchStock extends Model
{
    protected $table = 'product_branch_stock';

    protected $fillable = ['product_id', 'branch_id', 'quantity', 'avg_cost'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
