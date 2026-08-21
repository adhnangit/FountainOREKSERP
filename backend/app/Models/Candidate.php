<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_opening_id', 'first_name', 'last_name', 'email', 'phone', 'resume_path', 'cover_letter',
        'source', 'status', 'rating', 'notes', 'offered_salary', 'offer_date', 'employee_id', 'created_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'offered_salary' => 'decimal:2',
        'offer_date' => 'date',
    ];

    public function jobOpening(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function interviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CandidateInterview::class);
    }

    public function statusHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CandidateStatusHistory::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }
}
