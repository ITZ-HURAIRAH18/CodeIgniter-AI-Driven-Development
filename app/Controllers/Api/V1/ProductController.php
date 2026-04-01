<?php

namespace App\Controllers\Api\V1;

use App\Models\ProductModel;
use App\Models\ProductTranslationModel;

class ProductController extends BaseApiController
{
    private const SUPPORTED_LANGUAGES = ['en', 'ur', 'zh'];

    protected $model;
    protected $translationModel;

    public function __construct()
    {
        $this->model            = model(ProductModel::class);
        $this->translationModel = model(ProductTranslationModel::class);
    }

    /** GET /api/v1/products */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $search = $this->request->getGet('q');
            $status = $this->request->getGet('status');
            $lang   = $this->resolveLanguage($this->request->getGet('lang'));

            $query = $this->model->where('deleted_at', null);
            if ($search) $query->groupStart()->like('name', $search)->orLike('sku', $search)->groupEnd();
            if ($status) $query->where('status', $status);

            // For dashboard use, return simple array (no pagination)
            // Pagination not needed for dashboard - just need all products
            $products = $query->findAll();
            $ids      = array_column($products, 'id');

            $translationsByProduct = $this->getTranslationsByProductIds($ids);
            $localizedProducts     = array_map(function (array $product) use ($lang, $translationsByProduct) {
                return $this->withLocalizedFields($product, $lang, $translationsByProduct[$product['id']] ?? []);
            }, $products);

            return $this->ok($localizedProducts, 'Products retrieved.');
        } catch (\Exception $e) {
            log_message('error', 'ProductController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    /** GET /api/v1/products/{id} */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $lang = $this->resolveLanguage($this->request->getGet('lang'));
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);

        $translationsByProduct = $this->getTranslationsByProductIds([(int) $id]);
        $localizedProduct      = $this->withLocalizedFields($product, $lang, $translationsByProduct[(int) $id] ?? []);

        return $this->ok($localizedProduct);
    }

    /** POST /api/v1/products — admin only */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data         = $this->request->getJSON(true) ?? [];
        $translations = $data['translations'] ?? [];

        $translationErrors = $this->validateRequiredTranslations($translations);
        if (!empty($translationErrors)) {
            return $this->validationError($translationErrors);
        }

        $data['name']        = trim((string) ($translations['en']['name'] ?? ''));
        $data['description'] = trim((string) ($translations['en']['description'] ?? ''));

        if (!$this->model->validate($data)) {
            return $this->validationError($this->model->errors());
        }

        unset($data['translations']);

        $this->model->db->transStart();
        $id = (int) $this->model->insert($data, true);

        $translationRows = [];
        foreach (self::SUPPORTED_LANGUAGES as $language) {
            $translationRows[] = [
                'product_id'   => $id,
                'language'     => $language,
                'name'         => trim((string) $translations[$language]['name']),
                'description'  => trim((string) $translations[$language]['description']),
            ];
        }

        $this->translationModel->insertBatch($translationRows);
        $this->model->db->transComplete();

        if (!$this->model->db->transStatus()) {
            return $this->apiError('Failed to create product.', 500);
        }

        $lang = $this->resolveLanguage($this->request->getGet('lang'));
        $product = $this->model->find($id);
        $localizedProduct = $this->withLocalizedFields($product, $lang, $this->indexTranslations($translationRows));

        return $this->created($localizedProduct);
    }

    /** PUT /api/v1/products/{id} — admin only */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);

        $data         = $this->request->getJSON(true) ?? [];
        $translations = $data['translations'] ?? null;

        if (is_array($translations)) {
            $translationErrors = $this->validateRequiredTranslations($translations);
            if (!empty($translationErrors)) {
                return $this->validationError($translationErrors);
            }

            $data['name']        = trim((string) ($translations['en']['name'] ?? ''));
            $data['description'] = trim((string) ($translations['en']['description'] ?? ''));
        }

        unset($data['translations']);

        if (!$this->model->update($id, $data)) {
            return $this->validationError($this->model->errors());
        }

        if (is_array($translations)) {
            foreach (self::SUPPORTED_LANGUAGES as $language) {
                $translationData = [
                    'product_id'  => (int) $id,
                    'language'    => $language,
                    'name'        => trim((string) $translations[$language]['name']),
                    'description' => trim((string) $translations[$language]['description']),
                ];

                $existing = $this->translationModel
                    ->where('product_id', (int) $id)
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
        $translationsByProduct = $this->getTranslationsByProductIds([(int) $id]);
        $localizedProduct      = $this->withLocalizedFields($this->model->find($id), $lang, $translationsByProduct[(int) $id] ?? []);

        return $this->ok($localizedProduct, 'Product updated.');
    }

    /** DELETE /api/v1/products/{id} — admin only (soft delete) */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);

        $this->model->delete($id);
        return $this->ok(null, 'Product deleted.');
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

            if ($name === '') {
                $errors["translations.{$language}.name"] = strtoupper($language) . ' name is required.';
            }

            if ($description === '') {
                $errors["translations.{$language}.description"] = strtoupper($language) . ' description is required.';
            }
        }

        return $errors;
    }

    private function getTranslationsByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $rows = $this->translationModel
            ->whereIn('product_id', $productIds)
            ->whereIn('language', self::SUPPORTED_LANGUAGES)
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

    private function indexTranslations(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['language']] = [
                'name'        => $row['name'],
                'description' => $row['description'] ?? '',
            ];
        }

        return $indexed;
    }

    private function withLocalizedFields(array $product, string $language, array $translations): array
    {
        $selected = $translations[$language] ?? null;
        $english  = $translations['en'] ?? null;
        $fallback = $selected ?? $english;

        if ($fallback === null && !empty($translations)) {
            $fallback = reset($translations);
        }

        $product['name']             = $fallback['name'] ?? $product['name'] ?? '';
        $product['description']      = $fallback['description'] ?? $product['description'] ?? '';
        $product['display_language'] = $selected ? $language : ($english ? 'en' : null);
        $product['translations']     = $translations;

        return $product;
    }
}
