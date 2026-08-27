<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use SoftDeletes,HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'status',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
        ];
    }
}