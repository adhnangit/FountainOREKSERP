<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementRead extends Model
{
    public $timestamps = false;

    protected $fillable = ['announcement_id', 'user_id', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function announcement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
