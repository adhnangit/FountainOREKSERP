<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calendar_events';

    protected $fillable = [
        'branch_id', 'created_by', 'title', 'description', 'type',
        'start_at', 'end_at', 'all_day', 'color', 'location',
        'is_company_wide', 'linked_module', 'linked_id', 'reminder_sent',
        'reminder_minutes', 'recurrence', 'recurrence_days', 'recurrence_end_date',
    ];

    protected $casts = [
        'start_at'             => 'datetime',
        'end_at'               => 'datetime',
        'all_day'              => 'boolean',
        'is_company_wide'      => 'boolean',
        'reminder_sent'        => 'boolean',
        'recurrence_days'      => 'array',
        'recurrence_end_date'  => 'date:Y-m-d',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_event_attendees', 'event_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function completions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CalendarEventCompletion::class, 'event_id');
    }
}
