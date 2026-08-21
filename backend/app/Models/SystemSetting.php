<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = ['branch_id', 'key', 'value', 'group'];

    public static function get(string $key, ?int $branchId = null, mixed $default = null): mixed
    {
        $query = static::where('key', $key);
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })->orderByRaw('branch_id IS NULL');
        } else {
            $query->whereNull('branch_id');
        }
        $value = $query->value('value');
        return $value ?? $default;
    }

    public static function set(string $key, mixed $value, ?int $branchId = null, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key, 'branch_id' => $branchId],
            ['value' => $value, 'group' => $group]
        );
    }
}
