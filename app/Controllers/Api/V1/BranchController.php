<?php

namespace App\Controllers\Api\V1;

use App\Models\BranchModel;

class BranchController extends BaseApiController
{
    protected $model;

    public function __construct()
    {
        $this->model = model(BranchModel::class);
    }

    /** GET /api/v1/branches */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $actor = $this->actor();

            // Branch managers - see only their managed branches
            if ((int) $actor->role_id === 2) {
                $myBranchIds = $this->model->getManagerBranchIds((int)$actor->sub);
                log_message('debug', "BranchController::index - Manager ID: {$actor->sub}, Manages branches: " . implode(',', $myBranchIds));
                
                if (empty($myBranchIds)) {
                    log_message('warning', "BranchController::index - Manager {$actor->sub} has no assigned branches");
                    return $this->ok([]);
                }
                return $this->ok(
                    $this->model->whereIn('id', $myBranchIds)->findAll()
                );
            }

            // Sales users - see only their assigned branch
            if ((int) $actor->role_id === 3) {
                if (!$actor->branch_id) {
                    log_message('warning', "SalesController::index - Sales user {$actor->sub} has no assigned branch");
                    return $this->ok([]);
                }
                return $this->ok([$this->model->find((int)$actor->branch_id)]);
            }

            // Admins - see all branches without restriction
            return $this->ok($this->model->getWithManagers());
        } catch (\Exception $e) {
            log_message('error', 'BranchController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve branches: ' . $e->getMessage(), 500);
        }
    }

    /** GET /api/v1/branches/{id} */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);
        return $this->ok($branch);
    }

    /** POST /api/v1/branches — admin only */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->validate($data)) {
            return $this->validationError($this->model->errors());
        }

        // Validate manager_id is a branch manager
        if (isset($data['manager_id']) && $data['manager_id'] !== null) {
            $data['manager_id'] = (int) $data['manager_id'];
            $userModel = model(\App\Models\UserModel::class);
            $manager = $userModel->find($data['manager_id']);
            if (!$manager || (int)$manager->role_id !== 2) {
                return $this->apiError('Selected manager must have the Branch Manager role.', 400);
            }
        }

        // Insert branch with manager_id - this establishes the manager-branch relationship
        $id = $this->model->insert($data, true);
        return $this->created($this->model->find($id));
    }

    /** PUT /api/v1/branches/{id} — admin only */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $data = $this->request->getJSON(true);

        // Validate manager_id is a branch manager if provided
        if (isset($data['manager_id']) && $data['manager_id'] !== null) {
            $data['manager_id'] = (int) $data['manager_id'];
            $userModel = model(\App\Models\UserModel::class);
            $manager = $userModel->find($data['manager_id']);
            if (!$manager || (int)$manager->role_id !== 2) {
                return $this->apiError('Selected manager must have the Branch Manager role.', 400);
            }
        }

        // Update branch (only updates branches.manager_id, not users.branch_id)
        $this->model->update($id, $data);
        return $this->ok($this->model->find($id), 'Branch updated.');
    }

    /** DELETE /api/v1/branches/{id} — admin only */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        try {
            $this->model->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // FK violation — branch has stock or staff
            return $this->apiError(
                'Cannot delete branch: it still has inventory or associated users.',
                409
            );
        }

        return $this->ok(null, 'Branch deleted.');
    }

    /**
     * POST /api/v1/branches/{id}/assign-manager — Assign manager(s) to branch (admin only)
     * 
     * Request body:
     * {
     *   "manager_ids": [1, 2, 3]  // Array of manager IDs
     * }
     * 
     * NOTE: This replaces the current manager_id with ONE manager (FIFO from array).
     * TODO: Future work - implement pivot table for multiple managers per branch.
     */
    public function assignManager($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $data = $this->request->getJSON(true);

        if (empty($data['manager_ids'])) {
            return $this->apiError('manager_ids (array) is required.', 400);
        }

        if (!is_array($data['manager_ids']) || count($data['manager_ids']) === 0) {
            return $this->apiError('manager_ids must be a non-empty array.', 400);
        }

        try {
            $userModel = model(\App\Models\UserModel::class);

            // Validate all managers
            foreach ($data['manager_ids'] as $managerId) {
                $manager = $userModel->find($managerId);
                if (!$manager || (int)$manager->role_id !== 2) {
                    return $this->apiError("User {$managerId} is not a valid manager (role 2).", 400);
                }
            }

            // Assign first manager as primary (FIFO)
            // TODO: Implement pivot table for multiple managers
            $primaryManagerId = $data['manager_ids'][0];
            $this->model->update($id, ['manager_id' => $primaryManagerId]);

            $branch = $this->model->find($id);
            return $this->ok($branch, 'Manager(s) assigned to branch.');
        } catch (\Exception $e) {
            log_message('error', 'BranchController::assignManager: ' . $e->getMessage());
            return $this->apiError('Failed to assign manager: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/branches/{id}/managers — Get managers for a branch
     */
    public function getManagers($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        try {
            $userModel = model(\App\Models\UserModel::class);
            
            // For now, return single manager (current schema)
            // TODO: Update when pivot table implemented
            $managers = [];
            if (!empty($branch->manager_id)) {
                $manager = $userModel->find($branch->manager_id);
                if ($manager) {
                    $managers[] = $manager;
                }
            }

            return $this->ok($managers);
        } catch (\Exception $e) {
            log_message('error', 'BranchController::getManagers: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve managers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/v1/branches/{id}/remove-manager/{managerId} — Remove manager from branch
     */
    public function removeManager($id = null, $managerId = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        try {
            // If manager is the primary manager, remove assignment
            if ((int)$branch->manager_id === (int)$managerId) {
                $this->model->update($id, ['manager_id' => null]);
                return $this->ok($this->model->find($id), 'Manager removed from branch.');
            }

            return $this->apiError('Manager not assigned to this branch.', 404);
        } catch (\Exception $e) {
            log_message('error', 'BranchController::removeManager: ' . $e->getMessage());
            return $this->apiError('Failed to remove manager: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/branches/{id}/assign-sales — Assign sales user to branch
     * 
     * Admin: Can assign any sales user to any branch
     * Manager: Can assign sales users to branches they manage
     * 
     * Request body:
     * {
     *   "user_id": 5  // Sales user ID
     * }
     */
    public function assignSales($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $data = $this->request->getJSON(true);
        $actor = $this->actor();

        if (empty($data['user_id'])) {
            return $this->apiError('user_id is required.', 400);
        }

        try {
            $userModel = model(\App\Models\UserModel::class);
            $salesUser = $userModel->find($data['user_id']);

            if (!$salesUser) {
                return $this->apiError('User not found.', 404);
            }

            // Only sales users (role 3) can be assigned to branches
            if ((int)$salesUser->role_id !== 3) {
                return $this->apiError('Only sales users (role 3) can be assigned to branches.', 400);
            }

            $actorRoleId = (int) $actor->role_id;

            // Admin can assign any sales to any branch
            if ($actorRoleId === 1) {
                $userModel->update($data['user_id'], ['branch_id' => $id]);
                return $this->ok($userModel->find($data['user_id']), 'Sales user assigned to branch.');
            }

            // Manager can only assign sales to branches they manage
            if ($actorRoleId === 2) {
                $myBranchIds = $this->model->getManagerBranchIds((int)$actor->sub);
                if (!in_array((int)$id, $myBranchIds)) {
                    return $this->apiError('You can only assign users to branches you manage.', 403);
                }

                $userModel->update($data['user_id'], ['branch_id' => $id]);
                return $this->ok($userModel->find($data['user_id']), 'Sales user assigned to branch.');
            }

            return $this->apiError('You do not have permission to assign users to branches.', 403);
        } catch (\Exception $e) {
            log_message('error', 'BranchController::assignSales: ' . $e->getMessage());
            return $this->apiError('Failed to assign sales user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/branches/{id}/sales — Get all sales users assigned to branch
     * 
     * Admin: Can view sales in any branch
     * Manager: Can view sales in branches they manage
     */
    public function getSales($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        try {
            $actor = $this->actor();
            $actorRoleId = (int) $actor->role_id;

            // Manager can only view sales in branches they manage
            if ($actorRoleId === 2) {
                $myBranchIds = $this->model->getManagerBranchIds((int)$actor->sub);
                if (!in_array((int)$id, $myBranchIds)) {
                    return $this->apiError('You can only view sales users in branches you manage.', 403);
                }
            }

            $userModel = model(\App\Models\UserModel::class);
            $sales = $userModel->where('branch_id', $id)
                                ->where('role_id', 3)
                                ->where('deleted_at', null)
                                ->findAll();

            return $this->ok($sales);
        } catch (\Exception $e) {
            log_message('error', 'BranchController::getSales: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve sales users: ' . $e->getMessage(), 500);
        }
    }
}
