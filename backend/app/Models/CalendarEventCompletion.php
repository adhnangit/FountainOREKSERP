<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEventCompletion extends Model
{
    protected $table = 'calendar_event_completions';

    protected $fillable = ['event_id', 'occurrence_date', 'completed_by', 'completed_at'];

    protected $casts = [
        'occurrence_date' => 'date:Y-m-d',
        'completed_at'    => 'datetime',
    ];

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function completedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
