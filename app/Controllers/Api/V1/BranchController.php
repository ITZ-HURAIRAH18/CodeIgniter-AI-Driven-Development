<?php

namespace App\Controllers\Api\V1;

use App\Models\BranchModel;
use App\Models\BranchTranslationModel;

class BranchController extends BaseApiController
{
    private const SUPPORTED_LANGUAGES = ['en', 'ur', 'zh'];

    protected $model;
    protected $translationModel;

    public function __construct()
    {
        $this->model            = model(BranchModel::class);
        $this->translationModel = model(BranchTranslationModel::class);
    }

    /** GET /api/v1/branches */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $actor = $this->actor();
            $lang = $this->resolveLanguage($this->request->getGet('lang'));

            // Branch managers - see only their managed branches
            if ((int) $actor->role_id === 2) {
                $myBranchIds = $this->model->getManagerBranchIds((int)$actor->sub);
                log_message('debug', "BranchController::index - Manager ID: {$actor->sub}, Manages branches: " . implode(',', $myBranchIds));
                
                if (empty($myBranchIds)) {
                    log_message('warning', "BranchController::index - Manager {$actor->sub} has no assigned branches");
                    return $this->ok([]);
                }
                
                $branches = $this->model->whereIn('id', $myBranchIds)->findAll();
                $ids = array_column($branches, 'id');
                
                $translationsByBranch = $this->getTranslationsByBranchIds($ids);
                $localizedBranches = array_map(function (array $branch) use ($lang, $translationsByBranch) {
                    return $this->withLocalizedFields($branch, $lang, $translationsByBranch[$branch['id']] ?? []);
                }, $branches);
                
                return $this->ok($localizedBranches);
            }

            // Sales users and admins can see all branches
            $branches = $this->model->getWithManagers();
            $ids = array_column($branches, 'id');
            
            $translationsByBranch = $this->getTranslationsByBranchIds($ids);
            $localizedBranches = array_map(function (array $branch) use ($lang, $translationsByBranch) {
                return $this->withLocalizedFields($branch, $lang, $translationsByBranch[$branch['id']] ?? []);
            }, $branches);
            
            return $this->ok($localizedBranches);
        } catch (\Exception $e) {
            log_message('error', 'BranchController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve branches: ' . $e->getMessage(), 500);
        }
    }

    /** GET /api/v1/branches/{id} */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $lang = $this->resolveLanguage($this->request->getGet('lang'));
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $translationsByBranch = $this->getTranslationsByBranchIds([(int) $id]);
        $localizedBranch = $this->withLocalizedFields($branch, $lang, $translationsByBranch[(int) $id] ?? []);

        return $this->ok($localizedBranch);
    }

    /** POST /api/v1/branches — admin only */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $translations = $data['translations'] ?? [];

        $translationErrors = $this->validateRequiredTranslations($translations);
        if (!empty($translationErrors)) {
            return $this->validationError($translationErrors);
        }

        $data['name'] = trim((string) ($translations['en']['name'] ?? ''));
        $data['description'] = trim((string) ($translations['en']['description'] ?? ''));

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

        unset($data['translations']);

        $this->model->db->transStart();
        $id = (int) $this->model->insert($data, true);

        $translationRows = [];
        foreach (self::SUPPORTED_LANGUAGES as $language) {
            $translationRows[] = [
                'branch_id'   => $id,
                'language'    => $language,
                'name'        => trim((string) $translations[$language]['name']),
                'description' => trim((string) $translations[$language]['description']),
                'address'     => trim((string) $translations[$language]['address']),
            ];
        }

        $this->translationModel->insertBatch($translationRows);
        $this->model->db->transComplete();

        if (!$this->model->db->transStatus()) {
            return $this->apiError('Failed to create branch.', 500);
        }

        $lang = $this->resolveLanguage($this->request->getGet('lang'));
        $branch = $this->model->find($id);
        $localizedBranch = $this->withLocalizedFields($branch, $lang, $this->indexTranslations($translationRows));

        return $this->created($localizedBranch);
    }

    /** PUT /api/v1/branches/{id} — admin only */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $data = $this->request->getJSON(true) ?? [];
        $translations = $data['translations'] ?? null;

        if (is_array($translations)) {
            $translationErrors = $this->validateRequiredTranslations($translations);
            if (!empty($translationErrors)) {
                return $this->validationError($translationErrors);
            }

            $data['name'] = trim((string) ($translations['en']['name'] ?? ''));
            $data['description'] = trim((string) ($translations['en']['description'] ?? ''));
        }

        // Validate manager_id is a branch manager if provided
        if (isset($data['manager_id']) && $data['manager_id'] !== null) {
            $data['manager_id'] = (int) $data['manager_id'];
            $userModel = model(\App\Models\UserModel::class);
            $manager = $userModel->find($data['manager_id']);
            if (!$manager || (int)$manager->role_id !== 2) {
                return $this->apiError('Selected manager must have the Branch Manager role.', 400);
            }
        }

        unset($data['translations']);

        if (!$this->model->update($id, $data)) {
            return $this->validationError($this->model->errors());
        }

        if (is_array($translations)) {
            foreach (self::SUPPORTED_LANGUAGES as $language) {
                $translationData = [
                    'branch_id'   => (int) $id,
                    'language'    => $language,
                    'name'        => trim((string) $translations[$language]['name']),
                    'description' => trim((string) $translations[$language]['description']),
                    'address'     => trim((string) $translations[$language]['address']),
                ];

                $existing = $this->translationModel
                    ->where('branch_id', (int) $id)
                    ->where('language', $language)
                    ->first();

                if ($existing) {
                    $this->translationModel->update((int) $existing['id'], $translationData);
                } else {
                    $this->translationModel->insert($translationData);
                }
            }
        }

        $lang = $this->resolveLanguage($this->request->getGet('lang'));
        $translationsByBranch = $this->getTranslationsByBranchIds([(int) $id]);
        $localizedBranch = $this->withLocalizedFields($this->model->find($id), $lang, $translationsByBranch[(int) $id] ?? []);

        return $this->ok($localizedBranch, 'Branch updated.');
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

    private function resolveLanguage(?string $language): string
    {
        return in_array($language, self::SUPPORTED_LANGUAGES, true) ? $language : 'en';
    }

    private function validateRequiredTranslations(array $translations): array
    {
        $errors = [];

        foreach (self::SUPPORTED_LANGUAGES as $language) {
            $name = trim((string) ($translations[$language]['name'] ?? ''));
            $description = trim((string) ($translations[$language]['description'] ?? ''));
            $address = trim((string) ($translations[$language]['address'] ?? ''));

            if ($name === '') {
                $errors["translations.{$language}.name"] = strtoupper($language) . ' name is required.';
            }

            if ($description === '') {
                $errors["translations.{$language}.description"] = strtoupper($language) . ' description is required.';
            }

            if ($address === '') {
                $errors["translations.{$language}.address"] = strtoupper($language) . ' address is required.';
            }
        }

        return $errors;
    }

    private function getTranslationsByBranchIds(array $branchIds): array
    {
        if (empty($branchIds)) {
            return [];
        }

        $rows = $this->translationModel
            ->whereIn('branch_id', $branchIds)
            ->whereIn('language', self::SUPPORTED_LANGUAGES)
            ->findAll();

        $indexed = [];
        foreach ($rows as $row) {
            $branchId = (int) $row['branch_id'];
            $language = $row['language'];

            $indexed[$branchId][$language] = [
                'name'        => $row['name'],
                'description' => $row['description'] ?? '',
                'address'     => $row['address'] ?? '',
            ];
        }

        return $indexed;
    }

    private function indexTranslations(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['language']] = [
                'name'        => $row['name'],
                'description' => $row['description'] ?? '',
                'address'     => $row['address'] ?? '',
            ];
        }

        return $indexed;
    }

    private function withLocalizedFields(array $branch, string $language, array $translations): array
    {
        $selected = $translations[$language] ?? null;
        $english  = $translations['en'] ?? null;
        $fallback = $selected ?? $english;

        if ($fallback === null && !empty($translations)) {
            $fallback = reset($translations);
        }

        $branch['name']             = $fallback['name'] ?? $branch['name'] ?? '';
        $branch['description']      = $fallback['description'] ?? $branch['description'] ?? '';
        $branch['address']          = $fallback['address'] ?? $branch['address'] ?? '';
        $branch['display_language'] = $selected ? $language : ($english ? 'en' : null);
        $branch['translations']     = $translations;

        return $branch;
    }
}
