<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLogModel;
use App\Models\InventoryModel;
use App\Models\OrderModel;
use App\Services\OrderService;

class OrderController extends BaseApiController
{
    protected $service;
    protected $model;

    public function __construct()
    {
        $this->model   = model(OrderModel::class);
        $this->service = new OrderService(
            model(InventoryModel::class),
            model(InventoryLogModel::class),
            $this->model,
        );
    }

    /**
     * GET /api/v1/orders
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $actor    = $this->actor();
            $branchId = $this->request->getGet('branch_id');

            // Managers can see all their branches' orders
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);

                if ($branchId && !in_array((int)$branchId, $myBranchIds)) {
                     return $this->apiError('Access denied for this branch.', 403);
                }

                if (!$branchId) {
                    if (empty($myBranchIds)) return $this->ok([]);
                    return $this->ok($this->model->getWithDetailsMultiBranch($myBranchIds));
                }
            }

            // Sales users see only their assigned branch orders
            if ((int) $actor->role_id === 3) {
                 return $this->ok($this->model->getWithDetails((int) $actor->branch_id));
            }

            return $this->ok($this->model->getWithDetails($branchId ? (int) $branchId : null));
        } catch (\Exception $e) {
            log_message('error', 'OrderController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/orders/{id}
     */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $order = $this->model->find($id);
        if (!$order) return $this->apiError('Order not found.', 404);

        $items = \Config\Database::connect()
            ->table('order_items oi')
            ->select('oi.*, p.name AS product_name, p.sku')
            ->join('products p', 'p.id = oi.product_id')
            ->where('oi.order_id', $id)
            ->get()
            ->getResultArray();

        $order['items'] = $items;
        return $this->ok($order);
    }

    /**
     * POST /api/v1/orders
     * Atomically creates order and deducts stock.
     */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'branch_id'       => 'required|integer',
            'items'           => 'required',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity'   => 'required|integer|greater_than[0]',
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->validationError($this->validator->getErrors());
        }

        $actor = $this->actor();

        // Enforce branch based on role
        if ((int) $actor->role_id === 3) {
            // Sales user: must use their assigned branch
            $data['branch_id'] = $actor->branch_id;
        } elseif ((int) $actor->role_id === 2) {
            // Manager: must use one of their managed branches
            $branchModel = model(\App\Models\BranchModel::class);
            $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);

            if (!isset($data['branch_id']) || !in_array((int)$data['branch_id'], $myBranchIds)) {
                return $this->apiError('Invalid branch selection. You do not manage this branch.', 403);
            }
        }

        try {
            $order = $this->service->createOrder($data, (int) $actor->sub);
            return $this->created($order, 'Order placed successfully.');
        } catch (InsufficientStockException $e) {
            return $this->apiError($e->getMessage(), 422);
        } catch (\InvalidArgumentException $e) {
            return $this->apiError($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'OrderController::create: ' . $e->getMessage());
            return $this->apiError('Order processing failed. Please try again.', 500);
        }
    }

    /**
     * POST /api/v1/orders/{id}/cancel
     */
    public function cancel($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $order = $this->model->find($id);
        if (!$order) return $this->apiError('Order not found.', 404);

        if ($order['status'] === 'cancelled') {
            return $this->apiError('Order is already cancelled.', 400);
        }

        // Only admin can cancel a completed order
        if ($order['status'] === 'completed' && (int) $this->actor()->role_id !== 1) {
            return $this->apiError('Only admin can cancel completed orders.', 403);
        }

        $this->model->update($id, ['status' => 'cancelled']);
        return $this->ok(null, 'Order cancelled.');
    }
}
