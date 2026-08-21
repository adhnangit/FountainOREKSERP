<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'category_id', 'supplier_id', 'name', 'code', 'sku', 'brand', 'model',
        'unit', 'description', 'cost_price', 'selling_price', 'reorder_level',
        'valuation_method', 'track_serial', 'track_batch', 'has_expiry', 'image', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'track_serial' => 'boolean',
        'track_batch' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branchStocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductBranchStock::class);
    }

    public function batches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stockInBranch(int $branchId): float
    {
        $stock = $this->branchStocks()->where('branch_id', $branchId)->first();
        return $stock ? (float) $stock->quantity : 0.0;
    }

    public function movements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function invoiceItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseOrderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grnItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GrnItem::class);
    }

    public function purchaseReturnItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function transferItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function stockAdjustmentItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
