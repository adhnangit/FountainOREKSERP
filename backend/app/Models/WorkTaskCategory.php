<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTaskCategory extends Model
{
    protected $fillable = ['name', 'color', 'status', 'parent_id'];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkTask::class, 'category_id');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    /**
     * All descendant ids (children, grandchildren, ...), used so filtering by a
     * parent category also includes tasks filed under any of its sub-categories.
     */
    public function allDescendantIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->allDescendantIds());
        }
        return $ids;
    }

    /**
     * Flattens a collection of categories into a single ordered list (parent
     * immediately followed by its children, recursively), annotating each with
     * ->depth so views can indent sub-categories.
     */
    public static function buildTree($all, $parentId = null, $depth = 0)
    {
        $result = collect();
        foreach ($all->where('parent_id', $parentId) as $cat) {
            $cat->depth = $depth;
            $result->push($cat);
            $result = $result->merge(static::buildTree($all, $cat->id, $depth + 1));
        }
        return $result;
    }
}
