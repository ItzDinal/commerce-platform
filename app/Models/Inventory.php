<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends BaseModel
{
    protected $fillable = [
        'product_variant_id',
        'quantity',
        'reserved',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved' => 'integer',
        ];
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}