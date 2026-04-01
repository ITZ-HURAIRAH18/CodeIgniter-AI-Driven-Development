<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;
use App\Models\UserTranslationModel;

class UserController extends BaseApiController
{
    protected $model;
    protected $translationModel;

    public function __construct()
    {
        $this->model = model(UserModel::class);
        $this->translationModel = model(UserTranslationModel::class);
    }

    /**
     * GET /api/v1/users — List all users (active and inactive)
     * 
     * Query Parameters:
     * - lang=en|ur|zh : Language for user names (defaults to 'en')
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $lang = $this->request->getGet('lang') ?? 'en';
            
            $users = $this->model->where('deleted_at', null)
                                  ->select('id, name, email, role_id, is_active, last_login')
                                  ->findAll();
            
            return $this->ok($this->withLocalizedNames($users, $lang));
        } catch (\Exception $e) {
            log_message('error', 'UserController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/users — Create new user (admin only)
     * 
     * Request Body:
     * {
     *   "email": "user@company.com",
     *   "password": "securepass123",
     *   "role_id": 2,
     *   "translations": {
     *     "en": { "name": "User Name" },
     *     "ur": { "name": "صارف کا نام" },
     *     "zh": { "name": "用户名称" }
     *   }
     * }
     */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);
        
        // Validate required fields
        if (empty($data['email']) || empty($data['password']) || empty($data['role_id'])) {
            return $this->validationError([
                'email' => 'Email is required',
                'password' => 'Password is required',
                'role_id' => 'Role ID is required',
            ]);
        }
        
        // Validate translations
        if (empty($data['translations'])) {
            return $this->validationError(['translations' => 'Translations for all 3 languages (en, ur, zh) are required']);
        }
        
        $validationErrors = $this->validateRequiredTranslations($data['translations']);
        if (!empty($validationErrors)) {
            return $this->validationError($validationErrors);
        }

        $baseRules = [
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role_id'  => 'required|in_list[1,2,3]',
        ];

        if (!$this->validate($baseRules, $data)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $data['is_active'] = 1;
            
            // Extract translations before inserting user
            $translations = $data['translations'];
            unset($data['translations']);

            // Create user (use first language name as fallback)
            $data['name'] = $translations['en']['name'];
            $id = $this->model->insert($data, true);
            
            // Create translations
            foreach ($translations as $lang => $langData) {
                $this->translationModel->insert([
                    'user_id' => $id,
                    'language' => $lang,
                    'name' => $langData['name'],
                ]);
            }
            
            $user = $this->model->find($id);
            $user = $this->withLocalizedNames([$user], 'en')[0];

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
    /**
     * Validate that all 3 required languages have translations
     */
    private function validateRequiredTranslations($translations)
    {
        $errors = [];
        $requiredLanguages = ['en', 'ur', 'zh'];
        
        foreach ($requiredLanguages as $lang) {
            if (empty($translations[$lang])) {
                $errors[$lang] = "Translation for language '{$lang}' is required";
            } elseif (empty($translations[$lang]['name'])) {
                $errors["{$lang}.name"] = "Name for language '{$lang}' is required";
            } elseif (strlen($translations[$lang]['name']) < 2) {
                $errors["{$lang}.name"] = "Name must be at least 2 characters";
            }
        }
        
        return $errors;
    }

    /**
     * Add localized names to users based on language preference.
     * Falls back: Selected language -> English -> First available
     */
    private function withLocalizedNames(array $users, string $lang = 'en')
    {
        if (empty($users)) {
            return $users;
        }

        $userIds = array_map(fn($u) => $u->id, $users);
        $translations = $this->translationModel->getTranslationsByUserIds($userIds);

        foreach ($users as &$user) {
            $userTranslations = $translations[$user->id] ?? [];
            
            // Try selected language first
            if (isset($userTranslations[$lang])) {
                $user->name = $userTranslations[$lang];
            }
            // Fallback to English
            elseif (isset($userTranslations['en'])) {
                $user->name = $userTranslations['en'];
            }
            // Fallback to first available
            elseif (!empty($userTranslations)) {
                $user->name = reset($userTranslations);
            }
            // Keep original name if no translations
        }

        return $users;
    }
}
