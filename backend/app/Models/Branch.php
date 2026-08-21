<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'address', 'city', 'phone', 'email',
        'manager_name', 'is_active', 'is_head_office', 'settings', 'logo_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_head_office' => 'boolean',
        'settings' => 'array',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        // ?v= busts browser cache when a new logo replaces the old one
        return $this->logo_path
            ? url('/branch-logo/'.$this->id).'?v='.substr(md5($this->logo_path), 0, 8)
            : null;
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function targets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Target::class);
    }
}
