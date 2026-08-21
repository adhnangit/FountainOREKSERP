<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'goods_receipt_notes';

    protected $fillable = [
        'branch_id', 'purchase_order_id', 'supplier_id', 'created_by',
        'confirmed_by', 'grn_number', 'status', 'received_date',
        'delivery_note_number', 'notes', 'confirmed_at',
    ];

    protected $casts = [
        'received_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function supplierInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SupplierInvoice::class, 'grn_id');
    }
}
