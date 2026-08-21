<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $table = 'expense_categories';

    protected $fillable = ['name', 'code', 'description', 'account_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
