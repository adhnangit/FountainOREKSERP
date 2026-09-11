<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryStatus extends Model
{
    protected $fillable = ['name', 'color', 'order_by'];
}
