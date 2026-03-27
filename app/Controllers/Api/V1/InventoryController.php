<?php

namespace App\Controllers\Api\V1;

use App\Models\InventoryModel;
use App\Models\InventoryLogModel;
use App\Services\InventoryService;

class InventoryController extends BaseApiController
{
    private InventoryService $service;
    private InventoryModel   $model;

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
        $branchId = $this->request->getGet('branch_id');
        $actor    = $this->actor();

        // Branch managers can only see their own branch
        if ((int) $actor->role_id === 2 && !$branchId) {
            $branchId = $actor->branch_id;
        }

        if (!$branchId) {
            return $this->apiError('branch_id is required.', 400);
        }

        return $this->ok($this->model->getByBranch((int) $branchId));
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
