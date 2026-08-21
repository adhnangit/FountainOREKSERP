<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeDirectory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'office_directory';

    protected $fillable = [
        'branch_id', 'contact_type', 'name', 'designation', 'department',
        'company', 'category', 'phone', 'phone2', 'email', 'extension',
        'address', 'notes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
