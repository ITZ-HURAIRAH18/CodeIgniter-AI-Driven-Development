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

        $id = $this->model->insert($data, true);
        return $this->created($this->model->find($id));
    }

    /** PUT /api/v1/branches/{id} — admin only */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $branch = $this->model->find($id);
        if (!$branch) return $this->apiError('Branch not found.', 404);

        $data = $this->request->getJSON(true);
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
}
