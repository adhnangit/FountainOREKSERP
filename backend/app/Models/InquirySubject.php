<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquirySubject extends Model
{
    protected $fillable = ['name'];

    public function inquiries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Inquiry::class, 'subject', 'name');
    }
}
