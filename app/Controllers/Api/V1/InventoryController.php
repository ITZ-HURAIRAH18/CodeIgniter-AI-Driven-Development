<?php

namespace App\Controllers\Api\V1;

use App\Models\InventoryModel;
use App\Models\InventoryLogModel;
use App\Services\InventoryService;

class InventoryController extends BaseApiController
{
    protected $service;
    protected $model;

    public function __construct()
    {
        $this->model   = model(InventoryModel::class);
        $this->service = new InventoryService(
            $this->model,
            model(InventoryLogModel::class)
        );
    }

    /**
     * GET /api/v1/inventory?branch_id=X
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $branchId = $this->request->getGet('branch_id');
            $actor    = $this->actor();

            // Branch managers restricted to their own branches
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);

                if ($branchId && !in_array((int)$branchId, $myBranchIds)) {
                    return $this->apiError('Access denied: You do not manage this branch.', 403);
                }

                if (!$branchId) {
                    // Show all inventory for all my branches
                    if (empty($myBranchIds)) return $this->ok([]);
                    return $this->ok($this->model->whereIn('branch_id', $myBranchIds)->findAll());
                }
            }

            if (!$branchId && (int) $actor->role_id !== 1) {
                return $this->apiError('branch_id is required.', 400);
            }

            $data = $branchId ? $this->model->getByBranch((int) $branchId) : $this->model->findAll();
            return $this->ok($data);
        } catch (\Exception $e) {
            log_message('error', 'InventoryController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve inventory: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/inventory/branch/{id}
     */
    public function branch($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->ok($this->model->getByBranch((int) $id));
    }

    /**
     * POST /api/v1/inventory/add
     */
    public function add(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'branch_id'  => 'required|integer',
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $actor = $this->actor();

        try {
            $result = $this->service->addStock(
                branchId:  (int) $data['branch_id'],
                productId: (int) $data['product_id'],
                qty:       (int) $data['quantity'],
                actorId:   (int) $actor->sub,
                notes:     $data['notes'] ?? '',
            );
            return $this->ok($result, 'Stock added successfully.');
        } catch (\Throwable $e) {
            return $this->apiError($e->getMessage());
        }
    }

    /**
     * POST /api/v1/inventory/adjust
     */
    public function adjust(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'branch_id'  => 'required|integer',
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $actor = $this->actor();

        try {
            $result = $this->service->adjustStock(
                branchId:  (int) $data['branch_id'],
                productId: (int) $data['product_id'],
                newQty:    (int) $data['quantity'],
                actorId:   (int) $actor->sub,
                notes:     $data['notes'] ?? '',
            );
            return $this->ok($result, 'Stock adjusted successfully.');
        } catch (\Throwable $e) {
            return $this->apiError($e->getMessage());
        }
    }

    /**
     * GET /api/v1/inventory/logs?branch_id=X&product_id=Y
     */
    public function logs(): \CodeIgniter\HTTP\ResponseInterface
    {
        $branchId  = (int) $this->request->getGet('branch_id');
        $productId = (int) $this->request->getGet('product_id');

        $logModel = model(InventoryLogModel::class);
        $history  = $logModel->getHistory($branchId, $productId);

        return $this->ok($history);
    }
}
