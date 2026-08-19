<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function receive(
        Inventory $inventory,
        int $quantity,
        ?string $reason = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Received quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $inventory,
            $quantity,
            $reason
        ) {
            $inventory->increment('quantity', $quantity);

            return $inventory->movements()->create([
                'type' => 'received',
                'quantity' => $quantity,
                'reason' => $reason,
            ]);
        });
    }

    public function remove(
        Inventory $inventory,
        int $quantity,
        ?string $reason = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Removed quantity must be greater than zero.'
            );
        }

        if ($quantity > $inventory->available) {
            throw new RuntimeException(
                'Insufficient available inventory.'
            );
        }

        return DB::transaction(function () use (
            $inventory,
            $quantity,
            $reason
        ) {
            $inventory->decrement('quantity', $quantity);

            return $inventory->movements()->create([
                'type' => 'adjustment',
                'quantity' => -$quantity,
                'reason' => $reason,
            ]);
        });
    }
    public function reserve(
        Inventory $inventory,
        int $quantity
    ): void {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Reserved quantity must be greater than zero.'
            );
        }

        if ($quantity > $inventory->available) {
            throw new RuntimeException(
                'Insufficient available inventory.'
            );
        }

        DB::transaction(function () use ($inventory, $quantity) {
            $inventory->increment('reserved', $quantity);
        });
    }

    public function release(
        Inventory $inventory,
        int $quantity
    ): void {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Released quantity must be greater than zero.'
            );
        }

        if ($quantity > $inventory->reserved) {
            throw new RuntimeException(
                'Cannot release more inventory than currently reserved.'
            );
        }

        DB::transaction(function () use ($inventory, $quantity) {
            $inventory->decrement('reserved', $quantity);
        });
    }
}