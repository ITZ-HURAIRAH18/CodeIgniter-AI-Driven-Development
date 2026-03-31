<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table      = 'inventory';
    protected $primaryKey = 'id';

    // No timestamps on inventory (only updated_at via DB default)
    protected $useTimestamps = false;

    protected $allowedFields = ['branch_id', 'product_id', 'quantity', 'reorder_level'];

    /**
     * Get full inventory for a branch with product details.
     */
    public function getByBranch(int $branchId): array
    {
        return $this->db->table('inventory i')
            ->select('i.*, p.name AS product_name, p.sku, p.sale_price, p.unit, p.tax_percentage')
            ->join('products p', 'p.id = i.product_id')
            ->where('i.branch_id', $branchId)
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * Get all inventory across all branches with product details.
     */
    public function getAllWithProductDetails(): array
    {
        return $this->db->table('inventory i')
            ->select('i.*, p.name AS product_name, p.sku, p.sale_price, p.unit, p.tax_percentage, b.name AS branch_name')
            ->join('products p', 'p.id = i.product_id', 'left')
            ->join('branches b', 'b.id = i.branch_id', 'left')
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * Get inventory for specific branches with product details.
     */
    public function getByBranchesWithDetails(array $branchIds): array
    {
        if (empty($branchIds)) {
            return [];
        }
        return $this->db->table('inventory i')
            ->select('i.*, p.name AS product_name, p.sku, p.sale_price, p.unit, p.tax_percentage, b.name AS branch_name')
            ->join('products p', 'p.id = i.product_id', 'left')
            ->join('branches b', 'b.id = i.branch_id', 'left')
            ->whereIn('i.branch_id', $branchIds)
            ->where('p.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single inventory row — optionally with FOR UPDATE lock.
     * MUST be called inside an open transaction.
     */
    public function getRowForUpdate(int $branchId, int $productId): ?object
    {
        $sql = 'SELECT * FROM inventory WHERE branch_id = ? AND product_id = ? FOR UPDATE';
        $query = $this->db->query($sql, [$branchId, $productId]);

        return $query->getRow();
    }

    /**
     * Upsert inventory row (used when adding stock to a new branch/product combo).
     */
    public function upsert(int $branchId, int $productId, int $additional): bool
    {
        $existing = $this->where('branch_id', $branchId)
                         ->where('product_id', $productId)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'quantity'   => $existing['quantity'] + $additional,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return (bool) $this->insert([
            'branch_id'    => $branchId,
            'product_id'   => $productId,
            'quantity'     => $additional,
            'reorder_level' => 10,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
