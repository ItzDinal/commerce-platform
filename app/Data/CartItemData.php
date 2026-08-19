<?php

namespace App\Data;

class CartItemData
{
    public function __construct(
        public readonly string $id,
        public readonly string $productName,
        public readonly string $variantName,
        public readonly string $sku,
        public readonly int $quantity,
        public readonly int $unitPrice,
        public readonly int $lineTotal,
    ) {
    }
}