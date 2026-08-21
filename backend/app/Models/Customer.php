<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'account_id', 'assigned_user_id', 'code', 'name', 'company', 'type',
        'contact_person', 'phone', 'phone2', 'email', 'address', 'city', 'district',
        'country', 'tax_number', 'credit_limit', 'payment_terms_days',
        'opening_balance', 'credit_balance', 'bank_name', 'bank_account', 'notes', 'is_active',
        'is_walk_in',
    ];

    protected $casts = [
        'credit_limit'   => 'decimal:2',
        'opening_balance'=> 'decimal:2',
        'credit_balance' => 'decimal:2',
        'is_active'      => 'boolean',
        'is_walk_in'     => 'boolean',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function assignedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function cheques(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cheque::class, 'party_id')
            ->where('party_type', 'customer');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        $invoiceBalance = (float) $this->invoices()
            ->where('type', 'invoice') // exclude credit notes — they share this table but always carry balance_due=0
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->sum('balance_due');

        // Plus the account's opening balance (predates invoice-level tracking),
        // minus whatever's already been paid against it (Account::openingBalancePaid()
        // — opening_balance itself is never mutated, it stays a frozen historical
        // figure), minus any standing credit_balance (an over-return credit) —
        // matches the Customer Aging Report and list-page calculations, so every
        // view of a customer's balance agrees.
        $obRemaining = (float) ($this->account->opening_balance ?? 0) - ($this->account?->openingBalancePaid() ?? 0);
        return $invoiceBalance + $obRemaining - (float) $this->credit_balance;
    }

    public function getCreditUtilizationAttribute(): float
    {
        if ($this->credit_limit <= 0) return 0;
        return ($this->outstanding_balance / $this->credit_limit) * 100;
    }
}
