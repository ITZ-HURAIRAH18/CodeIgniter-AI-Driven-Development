<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransferModel extends Model
{
    protected $table         = 'stock_transfers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'from_branch_id', 'to_branch_id', 'initiated_by', 'status', 'notes',
    ];

    /**
     * Get transfer with branch names and initiator.
     */
    public function getWithDetails(int $id): ?array
    {
        $transfer = $this->db->table('stock_transfers st')
            ->select('st.*, fb.name AS from_branch, tb.name AS to_branch, u.name AS initiated_by_name')
            ->join('branches fb', 'fb.id = st.from_branch_id')
            ->join('branches tb', 'tb.id = st.to_branch_id')
            ->join('users u', 'u.id = st.initiated_by')
            ->where('st.id', $id)
            ->get()
            ->getRowArray();

        if (!$transfer) return null;

        $items = $this->db->table('stock_transfer_items sti')
            ->select('sti.*, p.name AS product_name, p.sku')
            ->join('products p', 'p.id = sti.product_id')
            ->where('sti.transfer_id', $id)
            ->get()
            ->getResultArray();

        $transfer['items'] = $items;
        return $transfer;
    }

    /**
     * List all transfers, optionally filtered by branch.
     */
    public function listAll(int $branchId = null): array
    {
        $builder = $this->db->table('stock_transfers st')
            ->select('st.*, fb.name AS from_branch, tb.name AS to_branch, u.name AS initiated_by_name')
            ->join('branches fb', 'fb.id = st.from_branch_id')
            ->join('branches tb', 'tb.id = st.to_branch_id')
            ->join('users u', 'u.id = st.initiated_by')
            ->orderBy('st.created_at', 'DESC');

        if ($branchId) {
            $builder->groupStart()
                ->where('st.from_branch_id', $branchId)
                ->orWhere('st.to_branch_id', $branchId)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }
}
