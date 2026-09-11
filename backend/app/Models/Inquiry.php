<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'source', 'message',
        'internal_notes', 'status', 'potential_value', 'assigned_to',
    ];

    protected $casts = [
        'potential_value' => 'decimal:2',
    ];

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InquiryFollowup::class)->orderByDesc('followup_date');
    }
}
