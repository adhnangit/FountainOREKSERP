<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplateItem extends Model
{
    protected $fillable = ['template_id', 'title', 'description', 'due_days_offset', 'sort_order'];

    protected $casts = ['due_days_offset' => 'integer', 'sort_order' => 'integer'];

    public function template(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }
}
