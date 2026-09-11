<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryFollowup extends Model
{
    protected $fillable = ['inquiry_id', 'followup_date', 'notes', 'user_id', 'outcome'];

    protected $casts = [
        'followup_date' => 'datetime',
    ];

    public function inquiry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
