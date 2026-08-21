<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'created_by', 'direction', 'party_id', 'party_type',
        'cheque_number', 'bank_name', 'bank_branch', 'cheque_date',
        'received_issued_date', 'amount', 'status', 'deposited_date',
        'cleared_date', 'bounced_date', 'bounce_reason', 'is_re_presented',
        'original_cheque_id', 'deposit_slip_number', 'deposit_account_id', 'notes', 'attachment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cheque_date' => 'date',
        'received_issued_date' => 'date',
        'deposited_date' => 'date',
        'cleared_date' => 'date',
        'bounced_date' => 'date',
        'is_re_presented' => 'boolean',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function depositAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'deposit_account_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function party(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('party', 'party_type', 'party_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'party_id');
    }

    public function invoiceLinks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChequeInvoiceLink::class);
    }

    public function supplierPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'received_cheque_id');
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isDueToday(): bool
    {
        return $this->cheque_date->isToday() && $this->status === 'in_hand';
    }

    public function isDueThisWeek(): bool
    {
        return $this->cheque_date->isCurrentWeek() && $this->status === 'in_hand';
    }
}
