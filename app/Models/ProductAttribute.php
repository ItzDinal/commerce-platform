<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_attribute'
        )->using(CategoryAttribute::class)
            ->withPivot([
                'is_required',
                'is_filterable',
                'sort_order',
            ]);
    }
}