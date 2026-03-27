<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;

class UserController extends BaseApiController
{
    protected $model;

    public function __construct()
    {
        $this->model = model(UserModel::class);
    }

    /**
     * GET /api/v1/users — List all active users (for dropdowns)
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $users = $this->model->where('is_active', 1)
                                  ->where('deleted_at', null)
                                  ->select('id, name, email, role_id, branch_id')
                                  ->findAll();
            
            return $this->ok($users);
        } catch (\Exception $e) {
            log_message('error', 'UserController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }
}
