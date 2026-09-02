<?php

namespace App\Models;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'account_id', 'code', 'name', 'company', 'contact_person', 'phone', 'phone2',
        'email', 'address', 'city', 'country', 'tax_number',
        'payment_terms_days', 'opening_balance', 'credit_balance', 'bank_name',
        'bank_account', 'bank_branch', 'product_categories', 'notes', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'product_categories' => 'array',
    ];

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierInvoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function cheques(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cheque::class, 'party_id')
            ->where('party_type', 'supplier');
    }

    public function purchaseReturns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * Deliberately the account's raw ledger balance, full stop — matches the
     * Chart of Accounts figure exactly, by construction, since it's the same
     * underlying calculation (Account::getBalanceAttribute()). Chosen over the
     * previous PO-balance-based formula specifically because that one counted
     * an approved purchase order the moment it was created, before its GRN
     * was confirmed — which made this figure diverge from the Chart of
     * Accounts (which only updates at GRN confirmation) for as long as any PO
     * was pending receipt. User confirmed they want the two numbers to always
     * agree, even at the cost of this no longer netting off a standing
     * credit_balance (an over-return credit) — that credit still exists and
     * is still usable when applying a future payment, it's simply not
     * subtracted from this headline figure anymore.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return (float) ($this->account->balance ?? 0);
    }
}
