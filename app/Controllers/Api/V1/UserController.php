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
     * GET /api/v1/users — List all users (active and inactive)
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $users = $this->model->where('deleted_at', null)
                                  ->select('id, name, email, role_id, is_active, last_login, date_of_birth')
                                  ->findAll();
            
            return $this->ok($users);
        } catch (\Exception $e) {
            log_message('error', 'UserController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/users — Create new user (admin only)
     * 
     * All users are created WITHOUT branch assigned.
     * Branch assignment happens separately via assignBranch endpoint.
     */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role_id'  => 'required|in_list[1,2,3]',
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            // All users created WITHOUT branch
            // Branch assignment happens separately

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $data['is_active'] = 1;

            $id = $this->model->insert($data, true);
            $user = $this->model->find($id);

            return $this->created($user, 'User created successfully.');
        } catch (\Exception $e) {
            log_message('error', 'UserController::create: ' . $e->getMessage());
            return $this->apiError('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/v1/users/{id} — Update user (admin only)
     * 
     * Can update: is_active, and other allowed fields
     * Cannot update: email (must be unique), password (use reset endpoint)
     */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->apiError('User not found.', 404);
        }

        $data = $this->request->getJSON(true);
        
        // Only allow updating these fields
        $allowedFields = ['is_active', 'name', 'role_id'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return $this->apiError('No valid fields to update.', 400);
        }

        try {
            $this->model->update($id, $updateData);
            $updatedUser = $this->model->find($id);
            return $this->ok($updatedUser, 'User updated successfully.');
        } catch (\Exception $e) {
            log_message('error', 'UserController::update: ' . $e->getMessage());
            return $this->apiError('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/v1/users/{id} — Delete user (soft delete, admin only)
     */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->apiError('User not found.', 404);
        }

        try {
            $this->model->delete($id);
            return $this->ok(null, 'User deleted successfully.');
        } catch (\Exception $e) {
            log_message('error', 'UserController::delete: ' . $e->getMessage());
            return $this->apiError('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/v1/users/{id}/assign-branch — Assign branch to user
     * 
     * Admin: Can assign any user to any branch
     * Manager: Can assign sales users to their managed branches only
     * 
     * @param int $id User ID
     */
    public function assignBranch($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->apiError('User not found.', 404);
        }

        $data = $this->request->getJSON(true);
        $actor = $this->actor();

        if (empty($data['branch_id'])) {
            return $this->apiError('branch_id is required.', 400);
        }

        try {
            $branchId = (int) $data['branch_id'];
            $userRoleId = (int) $user->role_id;
            $actorRoleId = (int) $actor->role_id;

            // Admins can assign any user to any branch
            if ($actorRoleId === 1) {
                $this->model->update($id, ['branch_id' => $branchId]);
                return $this->ok($this->model->find($id), 'Branch assigned to user.');
            }

            // Managers can only assign sales users (role 3) to their managed branches
            if ($actorRoleId === 2) {
                if ($userRoleId !== 3) {
                    return $this->apiError('Managers can only assign sales users to branches.', 403);
                }

                // Check if manager manages this branch
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
                if (!in_array($branchId, $myBranchIds)) {
                    return $this->apiError('You can only assign users to branches you manage.', 403);
                }

                $this->model->update($id, ['branch_id' => $branchId]);
                return $this->ok($this->model->find($id), 'Sales user assigned to branch.');
            }

            // Sales users cannot assign branches
            return $this->apiError('You do not have permission to assign branches.', 403);
        } catch (\Exception $e) {
            log_message('error', 'UserController::assignBranch: ' . $e->getMessage());
            return $this->apiError('Failed to assign branch: ' . $e->getMessage(), 500);
        }
    }
}
