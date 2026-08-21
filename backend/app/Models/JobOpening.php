<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOpening extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'department_id', 'designation_id', 'title', 'description', 'requirements',
        'employment_type', 'openings_count', 'status', 'posted_date', 'closing_date', 'created_by',
    ];

    protected $casts = [
        'openings_count' => 'integer',
        'posted_date' => 'date',
        'closing_date' => 'date',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function candidates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
