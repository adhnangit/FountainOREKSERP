<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $table = 'journal_entry_lines';

    protected $fillable = ['journal_entry_id', 'account_id', 'debit', 'credit', 'description'];

    protected $casts = ['debit' => 'decimal:2', 'credit' => 'decimal:2'];

    public function journalEntry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
