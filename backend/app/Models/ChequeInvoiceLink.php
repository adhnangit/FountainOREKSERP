<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeInvoiceLink extends Model
{
    protected $table = 'cheque_invoice_links';

    protected $fillable = ['cheque_id', 'invoice_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function cheque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cheque::class);
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
