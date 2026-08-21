<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'user_id', 'created_by', 'type', 'period', 'year',
        'period_number', 'target_value', 'achieved_value', 'alert_threshold_percent',
        'notes', 'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'achieved_value' => 'decimal:2',
        'alert_threshold_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAchievementPercentAttribute(): float
    {
        if ($this->target_value <= 0) return 0;
        return round(($this->achieved_value / $this->target_value) * 100, 2);
    }

    public function isUnderAlert(): bool
    {
        return $this->achievement_percent < $this->alert_threshold_percent;
    }
}
