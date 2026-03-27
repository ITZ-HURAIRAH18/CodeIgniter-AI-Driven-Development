<?php

namespace App\Services;

use App\Models\InventoryModel;
use App\Models\InventoryLogModel;
use RuntimeException;

/**
 * InventoryService
 * Handles manual stock additions and adjustments.
 */
class InventoryService
{
    public function __construct(
        private InventoryModel    $inventoryModel,
        private InventoryLogModel $logModel,
    ) {}

    /**
     * Add stock to a branch for a product.
     */
    public function addStock(int $branchId, int $productId, int $qty, int $actorId, string $notes = ''): array
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity to add must be positive.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $row = $this->inventoryModel->getRowForUpdate($branchId, $productId) ?? (object) ['quantity' => 0];

            $qtyBefore = $row->quantity ?? 0;
            $qtyAfter  = $qtyBefore + $qty;

            $this->inventoryModel->upsert($branchId, $productId, $qty);

            $this->logModel->record(
                branchId:  $branchId,
                productId: $productId,
                userId:    $actorId,
                type:      'add',
                qtyBefore: $qtyBefore,
                qtyChange: $qty,
                qtyAfter:  $qtyAfter,
                notes:     $notes,
            );

            $db->transComplete();

            return $this->inventoryModel->getByBranch($branchId);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Adjust (set) stock to a specific absolute value.
     */
    public function adjustStock(int $branchId, int $productId, int $newQty, int $actorId, string $notes = ''): array
    {
        if ($newQty < 0) {
            throw new \InvalidArgumentException('Adjusted quantity cannot be negative.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $row = $this->inventoryModel->getRowForUpdate($branchId, $productId);

            if (!$row) {
                throw new RuntimeException("Product #{$productId} is not stocked at branch #{$branchId}.");
            }

            $qtyBefore  = $row->quantity;
            $qtyChange  = $newQty - $qtyBefore;

            $db->table('inventory')
               ->where('id', $row->id)
               ->update(['quantity' => $newQty, 'updated_at' => date('Y-m-d H:i:s')]);

            $this->logModel->record(
                branchId:  $branchId,
                productId: $productId,
                userId:    $actorId,
                type:      'adjust',
                qtyBefore: $qtyBefore,
                qtyChange: $qtyChange,
                qtyAfter:  $newQty,
                notes:     $notes ?: "Manual adjustment to {$newQty}",
            );

            $db->transComplete();
            return $this->inventoryModel->getByBranch($branchId);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
