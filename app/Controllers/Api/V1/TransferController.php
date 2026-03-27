<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLogModel;
use App\Models\InventoryModel;
use App\Models\StockTransferModel;
use App\Services\StockTransferService;

class TransferController extends BaseApiController
{
    private StockTransferService $service;
    private StockTransferModel   $model;

    public function __construct()
    {
        $this->model   = model(StockTransferModel::class);
        $this->service = new StockTransferService(
            model(InventoryModel::class),
            model(InventoryLogModel::class),
            $this->model,
        );
    }

    /** GET /api/v1/transfers */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $actor    = $this->actor();
        $branchId = ($actor->role_id === 1) ? null : (int) $actor->branch_id;
        return $this->ok($this->model->listAll($branchId));
    }

    /** GET /api/v1/transfers/{id} */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $transfer = $this->model->getWithDetails((int) $id);
        if (!$transfer) return $this->apiError('Transfer not found.', 404);
        return $this->ok($transfer);
    }

    /** POST /api/v1/transfers */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'from_branch_id'     => 'required|integer',
            'to_branch_id'       => 'required|integer|differs[from_branch_id]',
            'items'              => 'required',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity'   => 'required|integer|greater_than[0]',
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->validationError($this->validator->getErrors());
        }

        try {
            $transfer = $this->service->create($data, (int) $this->actor()->sub);
            return $this->created($transfer);
        } catch (\InvalidArgumentException $e) {
            return $this->apiError($e->getMessage(), 400);
        } catch (\Throwable $e) {
            return $this->apiError($e->getMessage(), 500);
        }
    }

    /** POST /api/v1/transfers/{id}/approve */
    public function approve($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $transfer = $this->service->approve((int) $id, (int) $this->actor()->sub);
            return $this->ok($transfer, 'Transfer approved.');
        } catch (\RuntimeException $e) {
            return $this->apiError($e->getMessage(), 400);
        }
    }

    /** POST /api/v1/transfers/{id}/reject */
    public function reject($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $transfer = $this->service->reject((int) $id, (int) $this->actor()->sub);
            return $this->ok($transfer, 'Transfer rejected.');
        } catch (\RuntimeException $e) {
            return $this->apiError($e->getMessage(), 400);
        }
    }

    /** POST /api/v1/transfers/{id}/complete */
    public function complete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $transfer = $this->service->complete((int) $id, (int) $this->actor()->sub);
            return $this->ok($transfer, 'Transfer completed. Stock moved successfully.');
        } catch (InsufficientStockException $e) {
            return $this->apiError($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            return $this->apiError($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'TransferController::complete: ' . $e->getMessage());
            return $this->apiError('Transfer failed. Please try again.', 500);
        }
    }
}
