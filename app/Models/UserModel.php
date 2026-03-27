<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'role_id', 'branch_id', 'name', 'email',
        'password', 'is_active', 'last_login',
    ];

    protected $hidden = ['password'];

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role_id'  => 'required|integer|in_list[1,2,3]',
    ];

    /**
     * Find user by email for authentication.
     */
    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Update last login timestamp.
     */
    public function touchLastLogin(int $userId): void
    {
        $this->db->table($this->table)
                 ->where('id', $userId)
                 ->update(['last_login' => date('Y-m-d H:i:s')]);
    }
}
