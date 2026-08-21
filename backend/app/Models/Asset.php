<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code', 'name', 'category', 'branch_id', 'purchase_date', 'purchase_cost',
        'serial_number', 'status', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_date')->latestOfMany('assigned_date');
    }
}
