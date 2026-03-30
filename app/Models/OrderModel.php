<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table          = 'orders';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'branch_id', 'user_id', 'order_number', 'status',
        'subtotal', 'tax_amount', 'grand_total', 'notes',
    ];

    /**
     * Generate a sequential, unique order number per day.
     * Format: ORD-YYYYMMDD-XXXXX
     */
    public function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        $last   = $this->db->table('orders')
                           ->like('order_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->limit(1)
                           ->get()
                           ->getRow();

        $seq = $last ? ((int) substr($last->order_number, -5) + 1) : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get orders with branch and user info.
     */
    public function getWithDetails(int $branchId = null, int $createdById = null): array
    {
        $builder = $this->db->table('orders o')
            ->select('o.*, b.name AS branch_name, u.name AS created_by')
            ->join('branches b', 'b.id = o.branch_id')
            ->join('users u', 'u.id = o.user_id')
            ->where('o.deleted_at', null)
            ->orderBy('o.created_at', 'DESC');

        if ($branchId) {
            $builder->where('o.branch_id', $branchId);
        }

        if ($createdById) {
            $builder->where('o.user_id', $createdById);
        }

        return $builder->get()->getResultArray();
    }
    /**
     * Get orders for multiple specific branches.
     */
    public function getWithDetailsMultiBranch(array $branchIds): array
    {
        if (empty($branchIds)) return [];

        return $this->db->table('orders o')
            ->select('o.*, b.name AS branch_name, u.name AS created_by')
            ->join('branches b', 'b.id = o.branch_id')
            ->join('users u', 'u.id = o.user_id')
            ->where('o.deleted_at', null)
            ->whereIn('o.branch_id', $branchIds)
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
