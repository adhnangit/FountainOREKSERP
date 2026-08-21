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

    public function getOutstandingBalanceAttribute(): float
    {
        $poBalance = (float) $this->purchaseOrders()
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->where('status', '!=', 'cancelled')
            ->sum('balance_due');

        // Plus the account's opening balance (predates PO-level tracking),
        // minus whatever's already been paid against it (Account::openingBalancePaid()
        // — opening_balance itself is never mutated, it stays a frozen historical
        // figure), minus any standing credit_balance (an over-return credit) —
        // matches the Supplier Aging Report and list-page calculations, so every
        // view of a supplier's balance agrees.
        $obRemaining = (float) ($this->account->opening_balance ?? 0) - ($this->account?->openingBalancePaid() ?? 0);
        return $poBalance + $obRemaining - (float) $this->credit_balance;
    }
}
