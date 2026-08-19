<?php

namespace App\Data;

class CartData
{
    /**
     * @param CartItemData[] $items
     */
    public function __construct(
        public readonly string $id,
        public readonly array $items,
        public readonly int $totalItems,
        public readonly int $subtotal,
    ) {
    }
}