<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'scheduled_at', 'mode', 'interviewer_id', 'location_or_link',
        'status', 'feedback', 'rating', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function candidate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
