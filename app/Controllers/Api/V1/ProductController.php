<?php

namespace App\Controllers\Api\V1;

use App\Models\ProductModel;

class ProductController extends BaseApiController
{
    protected $model;

    public function __construct()
    {
        $this->model = model(ProductModel::class);
    }

    /** GET /api/v1/products */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $search = $this->request->getGet('q');
            $status = $this->request->getGet('status');

            $query = $this->model->where('deleted_at', null);
            if ($search) $query->groupStart()->like('name', $search)->orLike('sku', $search)->groupEnd();
            if ($status) $query->where('status', $status);

            return $this->ok($query->paginate(20), 'Products retrieved.');
        } catch (\Exception $e) {
            log_message('error', 'ProductController::index: ' . $e->getMessage());
            return $this->apiError('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    /** GET /api/v1/products/{id} */
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);
        return $this->ok($product);
    }

    /** POST /api/v1/products — admin only */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->model->validate($this->request->getJSON(true))) {
            return $this->validationError($this->model->errors());
        }

        $data = $this->request->getJSON(true);
        $id   = $this->model->insert($data, true);
        return $this->created($this->model->find($id));
    }

    /** PUT /api/v1/products/{id} — admin only */
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);

        $data = $this->request->getJSON(true);
        if (!$this->model->update($id, $data)) {
            return $this->validationError($this->model->errors());
        }

        return $this->ok($this->model->find($id), 'Product updated.');
    }

    /** DELETE /api/v1/products/{id} — admin only (soft delete) */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $product = $this->model->find($id);
        if (!$product) return $this->apiError('Product not found.', 404);

        $this->model->delete($id);
        return $this->ok(null, 'Product deleted.');
    }
}
