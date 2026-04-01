<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLogModel;
use App\Models\InventoryModel;
use App\Models\OrderModel;
use App\Services\OrderService;

class OrderController extends BaseApiController
{
    private const SUPPORTED_LANGUAGES = ['en', 'ur', 'zh'];
    
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
     * GET /api/v1/orders?lang=en|ur|zh
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $actor    = $this->actor();
            $branchId = $this->request->getGet('branch_id');
            $createdById = $this->request->getGet('created_by_id');
            $lang     = $this->resolveLanguage($this->request->getGet('lang'));

            // Admin: see all orders
            if ((int) $actor->role_id === 1) {
                $orders = $this->model->getWithDetails($branchId ? (int) $branchId : null);
                return $this->ok($this->applyBranchTranslations($orders, $lang));
            }

            // Branch Manager: see only orders from their managed branches
            if ((int) $actor->role_id === 2) {
                $branchModel = model(\App\Models\BranchModel::class);
                $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);

                // If manager has no branches assigned
                if (empty($myBranchIds)) {
                    return $this->ok([]);
                }

                // If a specific branch is requested, verify it's one of theirs
                if ($branchId) {
                    if (!in_array((int)$branchId, $myBranchIds)) {
                        return $this->apiError('Access denied for this branch.', 403);
                    }
                    $orders = $this->model->getWithDetails((int) $branchId);
                    return $this->ok($this->applyBranchTranslations($orders, $lang));
                }
                
                // Return orders from all their managed branches
                $orders = $this->model->getWithDetailsMultiBranch($myBranchIds);
                return $this->ok($this->applyBranchTranslations($orders, $lang));
            }

            // Sales User: see only their own orders
            if ((int) $actor->role_id === 3) {
                $orders = $this->model->getWithDetails(null, (int) $actor->sub);
                return $this->ok($this->applyBranchTranslations($orders, $lang));
            }

            $orders = $this->model->getWithDetails($branchId ? (int) $branchId : null);
            return $this->ok($this->applyBranchTranslations($orders, $lang));
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
        if ((int) $actor->role_id === 2) {
            // Manager: must use one of their managed branches
            $branchModel = model(\App\Models\BranchModel::class);
            $myBranchIds = $branchModel->getManagerBranchIds((int)$actor->sub);

            if (!isset($data['branch_id']) || !in_array((int)$data['branch_id'], $myBranchIds)) {
                return $this->apiError('Invalid branch selection. You do not manage this branch.', 403);
            }
        }
        // Admin can create orders for any branch (no restrictions)
        // Sales users can create orders for any branch

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

    private function resolveLanguage(?string $language): string
    {
        return in_array($language, self::SUPPORTED_LANGUAGES, true) ? $language : 'en';
    }

    private function applyBranchTranslations(array $orders, string $language): array
    {
        if (empty($orders)) {
            return [];
        }

        // Get all branch IDs
        $branchIds = array_unique(array_column($orders, 'branch_id'));
        
        // Get translations for all branches
        $translationsByBranch = $this->getBranchTranslationsByIds($branchIds, $language);

        // Apply localized branch names to each order
        return array_map(function (array $order) use ($translationsByBranch) {
            $branchId = (int) $order['branch_id'];
            $order['branch_name'] = $translationsByBranch[$branchId] ?? $order['branch_name'] ?? '';
            return $order;
        }, $orders);
    }

    private function getBranchTranslationsByIds(array $branchIds, string $language): array
    {
        if (empty($branchIds)) {
            return [];
        }

        $translationModel = model(\App\Models\BranchTranslationModel::class);
        
        // First try to get the requested language
        $rows = $translationModel
            ->whereIn('branch_id', $branchIds)
            ->where('language', $language)
            ->findAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['branch_id']] = $row['name'];
        }

        // For missing branches, fallback to English
        $missingIds = array_diff($branchIds, array_keys($indexed));
        if (!empty($missingIds)) {
            $fallbackRows = $translationModel
                ->whereIn('branch_id', $missingIds)
                ->where('language', 'en')
                ->findAll();

            foreach ($fallbackRows as $row) {
                $indexed[(int) $row['branch_id']] = $row['name'];
            }
        }

        return $indexed;
    }
}
