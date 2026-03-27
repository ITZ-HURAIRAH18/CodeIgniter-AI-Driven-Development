<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table          = 'branches';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'manager_id', 'name', 'address', 'phone', 'is_active',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[branches.name,id,{id}]',
    ];

    /**
     * Return all active branches with manager name joined.
     */
    public function getWithManagers(): array
    {
        return $this->db->table('branches b')
            ->select('b.*, u.name AS manager_name, u.email AS manager_email')
            ->join('users u', 'u.id = b.manager_id', 'left')
            ->whereNull('b.deleted_at')
            ->get()
            ->getResultArray();
    }
}
