<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Category extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'category_attribute'
        )->using(CategoryAttribute::class)
            ->withPivot([
                'is_required',
                'is_filterable',
                'sort_order',
            ]);
    }
}