<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleWidgetSetting extends Model
{
    protected $fillable = ['role_name', 'widget_key', 'is_visible', 'sort_order'];

    protected $casts = ['is_visible' => 'boolean'];

    public static function visibleWidgetsForRole(string $roleName): array
    {
        return static::where('role_name', $roleName)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->pluck('widget_key')
            ->toArray();
    }

    public static function settingsForRole(string $roleName): array
    {
        return static::where('role_name', $roleName)
            ->orderBy('sort_order')
            ->get(['widget_key', 'is_visible', 'sort_order'])
            ->toArray();
    }
}
