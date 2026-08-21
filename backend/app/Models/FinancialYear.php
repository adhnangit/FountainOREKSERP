<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialYear extends Model
{
    protected $table = 'financial_years';

    protected $fillable = ['name', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function journalEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public static function active(): ?self
    {
        return static::where('status', 'active')->first();
    }
}
