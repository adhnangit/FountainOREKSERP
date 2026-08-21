<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'supplier_id', 'created_by', 'approved_by', 'po_number',
        'status', 'order_date', 'invoice_date', 'expected_date', 'due_date',
        'subtotal', 'tax_amount', 'discount_amount', 'total',
        'paid_amount', 'balance_due', 'payment_status',
        'approval_threshold', 'notes', 'terms', 'approved_at',
        'payment_method', 'payment_terms_days', 'reference', 'supplier_invoice_ref',
        'account_id', 'cheque_type', 'cheque_number', 'cheque_bank_name', 'cheque_date', 'received_cheque_id',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'invoice_date'  => 'date',
        'expected_date' => 'date',
        'due_date'      => 'date',
        'cheque_date'   => 'date',
        'subtotal'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'         => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'balance_due'   => 'decimal:2',
        'approved_at'   => 'datetime',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoodsReceiptNote::class, 'purchase_order_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'purchase_order_id');
    }
}
