<?php

namespace App\Controllers\Api\V1;

use App\Models\InventoryModel;
use App\Models\InventoryLogModel;
use App\Models\ProductTranslationModel;
use App\Models\BranchTranslationModel;
use App\Services\InventoryService;

class InventoryController extends BaseApiController
{
    private const SUPPORTED_LANGUAGES = ['en', 'ur', 'zh'];
    
    protected $service;
    protected $model;
    protected $translationModel;

    public function __construct()
    {
        $this->model   = model(InventoryModel::class);
        $this->translationModel = model(ProductTranslationModel::class);
        $this->service = new InventoryService(
            $this->model,
            model(InventoryLogModel::class)
        );
    }

    /**
     * GET /api/v1/inventory?branch_id=X&lang=en|ur|zh
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $branchId = $this->request->getGet('branch_id');
            $lang     = $this->resolveLanguage($this->request->getGet('lang'));
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
                    $data = $this->model->getByBranchesWithDetails($myBranchIds);
                    return $this->ok($this->withLocalizedProducts($data, $lang));
                }
                
                // Manager requested specific branch - get that branch's inventory
                $data = $this->model->getByBranch((int) $branchId);
                return $this->ok($this->withLocalizedProducts($data, $lang));
            }

            // Sales users and admins can view inventory from ALL branches
            $data = $branchId ? $this->model->getByBranch((int) $branchId) : $this->model->getAllWithProductDetails();
            return $this->ok($this->withLocalizedProducts($data, $lang));
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
        $lang      = $this->resolveLanguage($this->request->getGet('lang'));

        $logModel = model(InventoryLogModel::class);
        
        // If both parameters provided, get history for specific branch/product
        if ($branchId > 0 && $productId > 0) {
            $history = $logModel->getHistory($branchId, $productId);
            // Apply translations to history
            $history = $this->applyLogTranslations($history, $lang);
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
        
        // Apply translations to logs
        $logs = $this->applyLogTranslations(array_values($logs), $lang);
        
        return $this->ok($logs);
    }

    /**
     * Apply localized product names to inventory data
     */
    private function withLocalizedProducts(array $inventoryData, string $language): array
    {
        if (empty($inventoryData)) {
            return [];
        }

        // Get all product IDs from inventory
        $productIds = array_unique(array_column($inventoryData, 'product_id'));
        
        // Get translations for all products
        $translationsByProduct = $this->getTranslationsByProductIds($productIds);

        // Apply localized fields to each inventory item
        return array_map(function (array $item) use ($language, $translationsByProduct) {
            $productId = (int) $item['product_id'];
            $translations = $translationsByProduct[$productId] ?? [];
            
            // Get the requested language translation, fallback to English, then first available
            $selected = $translations[$language] ?? null;
            $english  = $translations['en'] ?? null;
            $fallback = $selected ?? $english;

            if ($fallback === null && !empty($translations)) {
                $fallback = reset($translations);
            }

            // Update product_name with localized version
            $item['product_name'] = $fallback['name'] ?? $item['product_name'] ?? '';
            
            return $item;
        }, $inventoryData);
    }

    /**
     * Get translations for products by their IDs
     */
    private function getTranslationsByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $rows = $this->translationModel
            ->whereIn('product_id', $productIds)
            ->findAll();

        $indexed = [];
        foreach ($rows as $row) {
            $productId = (int) $row['product_id'];
            $language  = $row['language'];

            $indexed[$productId][$language] = [
                'name'        => $row['name'],
                'description' => $row['description'] ?? '',
            ];
        }

        return $indexed;
    }

    private function resolveLanguage(?string $language): string
    {
        return in_array($language, self::SUPPORTED_LANGUAGES, true) ? $language : 'en';
    }

    /**
     * Apply translations to inventory logs (product names and branch names)
     */
    private function applyLogTranslations(array $logs, string $language): array
    {
        if (empty($logs)) {
            return [];
        }

        // Get all product IDs from logs
        $productIds = array_unique(array_column($logs, 'product_id'));
        $branchIds = array_unique(array_column($logs, 'branch_id'));
        
        // Get product translations
        $productTranslations = $this->getTranslationsByProductIds($productIds);
        
        // Get branch translations
        $branchTranslations = $this->getTranslationsByBranchIds($branchIds);

        // Apply localized fields to each log item
        return array_map(function (array $log) use ($language, $productTranslations, $branchTranslations) {
            $productId = (int) ($log['product_id'] ?? 0);
            $branchId = (int) ($log['branch_id'] ?? 0);
            
            // Get translated product name
            $productTranslation = $productTranslations[$productId] ?? [];
            $selected = $productTranslation[$language] ?? null;
            $english = $productTranslation['en'] ?? null;
            $fallback = $selected ?? $english;
            
            if ($fallback === null && !empty($productTranslation)) {
                $fallback = reset($productTranslation);
            }
            
            $log['product_name'] = $fallback['name'] ?? $log['product_name'] ?? '';
            
            // Get translated branch name
            $branchTranslation = $branchTranslations[$branchId] ?? [];
            $selected = $branchTranslation[$language] ?? null;
            $english = $branchTranslation['en'] ?? null;
            $fallback = $selected ?? $english;
            
            if ($fallback === null && !empty($branchTranslation)) {
                $fallback = reset($branchTranslation);
            }
            
            $log['branch_name'] = $fallback['name'] ?? $log['branch_name'] ?? '';
            
            return $log;
        }, $logs);
    }

    /**
     * Get translations for branches by their IDs
     */
    private function getTranslationsByBranchIds(array $branchIds): array
    {
        if (empty($branchIds)) {
            return [];
        }

        $branchTranslationModel = model(BranchTranslationModel::class);
        $rows = $branchTranslationModel
            ->whereIn('branch_id', $branchIds)
            ->findAll();

        $indexed = [];
        foreach ($rows as $row) {
            $branchId = (int) $row['branch_id'];
            $language = $row['language'];

            $indexed[$branchId][$language] = [
                'name' => $row['name'],
                'address' => $row['address'] ?? '',
            ];
        }

        return $indexed;
    }
}
