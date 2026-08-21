<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'default_branch_id', 'phone',
        'designation', 'department', 'employee_id', 'avatar',
        'is_active', 'is_super_admin', 'two_factor_enabled',
        'two_factor_secret', 'allowed_ips', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_super_admin' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function defaultBranch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'default_branch_id');
    }

    public function branches(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function appNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function dashboardWidgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DashboardWidget::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin || $this->hasRole('super_admin');
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->branches()->where('branch_id', $branchId)->exists();
    }
}
