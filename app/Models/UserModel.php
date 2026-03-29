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
        'role_id', 'name', 'email',
        'password', 'is_active', 'last_login', 'date_of_birth',
    ];

    protected $hidden = ['password'];

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role_id'  => 'required|integer|in_list[1,2,3]',
        'date_of_birth' => 'permit_empty|valid_date[Y-m-d]',
        'is_active' => 'integer|in_list[0,1]',
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

    /**
     * Calculate age from date_of_birth
     */
    public function calculateAge(?string $dateOfBirth): ?int
    {
        if (!$dateOfBirth) {
            return null;
        }

        $birthDate = new \DateTime($dateOfBirth);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;
        
        return $age;
    }
}
