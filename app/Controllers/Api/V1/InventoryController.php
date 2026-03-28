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

            // Branch managers - can view all their branches' inventory
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
                
                log_message('debug', "InventoryController::index - Manager ID: {$actor->sub}, Manages branches: " . implode(',', $myBranchIds));

                if ($branchId && !in_array((int)$branchId, $myBranchIds)) {
                    log_message('warning', "InventoryController::index - Manager {$actor->sub} denied access to branch {$branchId}");
                    return $this->apiError('Access denied: You do not manage this branch.', 403);
                }

                if (!$branchId) {
                    // Show all inventory for all managed branches with product details
                    if (empty($myBranchIds)) return $this->ok([]);
                    return $this->ok($this->model->getByBranchesWithDetails($myBranchIds));
                }
                
                // Manager requested specific branch - get that branch's inventory
                return $this->ok($this->model->getByBranch((int) $branchId));
            }

            // Sales users and admins can view inventory from ALL branches
            $data = $branchId ? $this->model->getByBranch((int) $branchId) : $this->model->getAllWithProductDetails();
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
            // Branch managers can only add stock to their own branches
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
                if (!in_array((int)$data['branch_id'], $myBranchIds)) {
                    return $this->apiError('Access denied: You cannot add stock to this branch.', 403);
                }
            }

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
            // Branch managers can only adjust stock in their own branches
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
                if (!in_array((int)$data['branch_id'], $myBranchIds)) {
                    return $this->apiError('Access denied: You cannot adjust stock in this branch.', 403);
                }
            }

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
     * Or GET /api/v1/inventory/logs (returns recent logs for dashboard)
     */
    public function logs(): \CodeIgniter\HTTP\ResponseInterface
    {
        $branchId  = (int) $this->request->getGet('branch_id');
        $productId = (int) $this->request->getGet('product_id');

        $logModel = model(InventoryLogModel::class);
        
        // If both parameters provided, get history for specific branch/product
        if ($branchId > 0 && $productId > 0) {
            $history = $logModel->getHistory($branchId, $productId);
            return $this->ok($history);
        }
        
        // Otherwise, get recent logs across all data (for dashboard) with product details
        $actor = $this->actor();
        $logs = $logModel->getRecentLogs(50);
        
        // If branch manager, filter to their branches only
        if ((int) $actor->role_id === 2) {
            $branchModel = model(\App\Models\BranchModel::class);
            $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);
            $logs = array_filter($logs, fn($log) => in_array($log['branch_id'], $myBranchIds));
        }
        
        return $this->ok(array_values($logs));
    }
}
