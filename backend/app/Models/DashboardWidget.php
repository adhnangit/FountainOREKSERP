<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $table = 'dashboard_widgets';

    protected $fillable = ['user_id', 'widget_key', 'is_visible', 'sort_order', 'config'];

    protected $casts = [
        'is_visible' => 'boolean',
        'config' => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
