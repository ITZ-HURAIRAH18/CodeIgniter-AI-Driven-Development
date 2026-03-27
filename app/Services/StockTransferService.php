<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLogModel;
use App\Models\InventoryModel;
use App\Models\StockTransferModel;
use RuntimeException;

/**
 * StockTransferService
 *
 * Manages the full lifecycle of cross-branch stock transfers:
 * create → approve → complete
 *
 * Deadlock prevention: When completing a transfer, inventory rows are
 * always locked in ascending branch_id order to prevent circular deadlocks
 * under concurrent transfers.
 */
class StockTransferService
{
    public function __construct(
        private InventoryModel    $inventoryModel,
        private InventoryLogModel $logModel,
        private StockTransferModel $transferModel,
    ) {}

    /**
     * Create a pending transfer request.
     *
     * @param array $data { from_branch_id, to_branch_id, items:[{product_id, quantity}], notes }
     * @param int   $actorId
     */
    public function create(array $data, int $actorId): array
    {
        if ($data['from_branch_id'] === $data['to_branch_id']) {
            throw new \InvalidArgumentException('Source and destination branches must differ.');
        }

        if (empty($data['items'])) {
            throw new \InvalidArgumentException('Transfer must include at least one product.');
        }

        $db = $this->transferModel->db;
        $db->transStart();

        try {
            $transferId = $this->transferModel->insert([
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id'   => $data['to_branch_id'],
                'initiated_by'   => $actorId,
                'status'         => 'pending',
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $db->table('stock_transfer_items')->insert([
                    'transfer_id' => $transferId,
                    'product_id'  => $item['product_id'],
                    'quantity'    => (int) $item['quantity'],
                ]);
            }

            $db->transComplete();
            return $this->transferModel->getWithDetails($transferId);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Approve a pending transfer.
     */
    public function approve(int $transferId, int $actorId): array
    {
        $transfer = $this->transferModel->find($transferId);
        if (!$transfer) throw new \RuntimeException('Transfer not found.');
        if ($transfer['status'] !== 'pending') throw new \RuntimeException('Transfer is not in pending status.');

        $this->transferModel->update($transferId, ['status' => 'approved']);
        return $this->transferModel->getWithDetails($transferId);
    }

    /**
     * Complete an approved transfer — deducts from source, adds to destination.
     * Uses pessimistic locking with consistent lock ordering to prevent deadlocks.
     */
    public function complete(int $transferId, int $actorId): array
    {
        $transfer = $this->transferModel->getWithDetails($transferId);

        if (!$transfer) {
            throw new \RuntimeException('Transfer not found.');
        }
        if ($transfer['status'] !== 'approved') {
            throw new \RuntimeException('Transfer must be approved before completing.');
        }

        $fromBranchId = (int) $transfer['from_branch_id'];
        $toBranchId   = (int) $transfer['to_branch_id'];

        $db = $this->inventoryModel->db;
        $db->transStart();

        try {
            foreach ($transfer['items'] as $item) {
                $productId = (int) $item['product_id'];
                $qty       = (int) $item['quantity'];

                // ── DEADLOCK PREVENTION: always lock lower branch_id first ────
                if ($fromBranchId < $toBranchId) {
                    $fromRow = $this->inventoryModel->getRowForUpdate($fromBranchId, $productId);
                    $toRow   = $this->inventoryModel->getRowForUpdate($toBranchId, $productId);
                } else {
                    $toRow   = $this->inventoryModel->getRowForUpdate($toBranchId, $productId);
                    $fromRow = $this->inventoryModel->getRowForUpdate($fromBranchId, $productId);
                }

                if (!$fromRow || $fromRow->quantity < $qty) {
                    throw new InsufficientStockException(
                        "Insufficient stock for product #{$productId} at source branch."
                    );
                }

                $fromBefore = $fromRow->quantity;
                $fromAfter  = $fromBefore - $qty;
                $toBefore   = $toRow ? (int) $toRow->quantity : 0;
                $toAfter    = $toBefore + $qty;

                // Deduct from source
                $db->table('inventory')
                   ->where('id', $fromRow->id)
                   ->update(['quantity' => $fromAfter, 'updated_at' => date('Y-m-d H:i:s')]);

                // Add to destination (upsert)
                if ($toRow) {
                    $db->table('inventory')
                       ->where('id', $toRow->id)
                       ->update(['quantity' => $toAfter, 'updated_at' => date('Y-m-d H:i:s')]);
                } else {
                    $db->table('inventory')->insert([
                        'branch_id'  => $toBranchId,
                        'product_id' => $productId,
                        'quantity'   => $qty,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // Ledger: source transfer_out
                $this->logModel->record(
                    branchId:  $fromBranchId,
                    productId: $productId,
                    userId:    $actorId,
                    type:      'transfer_out',
                    qtyBefore: $fromBefore,
                    qtyChange: -$qty,
                    qtyAfter:  $fromAfter,
                    refType:   'transfer',
                    refId:     $transferId,
                );

                // Ledger: destination transfer_in
                $this->logModel->record(
                    branchId:  $toBranchId,
                    productId: $productId,
                    userId:    $actorId,
                    type:      'transfer_in',
                    qtyBefore: $toBefore,
                    qtyChange: $qty,
                    qtyAfter:  $toAfter,
                    refType:   'transfer',
                    refId:     $transferId,
                );
            }

            $this->transferModel->update($transferId, ['status' => 'completed']);
            $db->transComplete();

            return $this->transferModel->getWithDetails($transferId);

        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Reject a pending transfer.
     */
    public function reject(int $transferId, int $actorId): array
    {
        $transfer = $this->transferModel->find($transferId);
        if (!$transfer) throw new \RuntimeException('Transfer not found.');
        if ($transfer['status'] !== 'pending') throw new \RuntimeException('Only pending transfers can be rejected.');

        $this->transferModel->update($transferId, ['status' => 'rejected']);
        return $this->transferModel->getWithDetails($transferId);
    }
}
