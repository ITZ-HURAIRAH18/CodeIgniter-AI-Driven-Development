<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryLogModel extends Model
{
    protected $table      = 'inventory_logs';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;  // manual created_at

    protected $allowedFields = [
        'branch_id', 'product_id', 'user_id', 'movement_type',
        'reference_type', 'reference_id',
        'qty_before', 'qty_change', 'qty_after',
        'notes', 'created_at',
    ];

    /**
     * Write a stock movement to the immutable ledger.
     */
    public function record(
        int    $branchId,
        int    $productId,
        int    $userId,
        string $type,
        int    $qtyBefore,
        int    $qtyChange,
        int    $qtyAfter,
        string $refType  = null,
        int    $refId    = null,
        string $notes    = null
    ): int {
        return $this->insert([
            'branch_id'      => $branchId,
            'product_id'     => $productId,
            'user_id'        => $userId,
            'movement_type'  => $type,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'qty_before'     => $qtyBefore,
            'qty_change'     => $qtyChange,
            'qty_after'      => $qtyAfter,
            'notes'          => $notes,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get movement history for a branch/product pair.
     */
    public function getHistory(int $branchId, int $productId, int $limit = 50): array
    {
        return $this->where('branch_id', $branchId)
                    ->where('product_id', $productId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get recent logs with product details for dashboard.
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->db->table('inventory_logs il')
                    ->select('il.*, p.name AS product_name, b.name AS branch_name')
                    ->join('products p', 'p.id = il.product_id', 'left')
                    ->join('branches b', 'b.id = il.branch_id', 'left')
                    ->orderBy('il.created_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
    }
}
