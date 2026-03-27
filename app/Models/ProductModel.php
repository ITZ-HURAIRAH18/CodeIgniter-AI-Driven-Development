<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table          = 'products';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'sku', 'name', 'description', 'cost_price',
        'sale_price', 'tax_percentage', 'unit', 'status',
    ];

    protected $validationRules = [
        'sku'            => 'required|alpha_dash|max_length[100]|is_unique[products.sku,id,{id}]',
        'name'           => 'required|min_length[2]|max_length[200]',
        'cost_price'     => 'required|decimal|greater_than_equal_to[0]',
        'sale_price'     => 'required|decimal|greater_than_equal_to[0]',
        'tax_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];

    /**
     * Search products by name or SKU using fulltext.
     */
    public function search(string $term): array
    {
        return $this->whereNull('deleted_at')
                    ->groupStart()
                        ->like('name', $term)
                        ->orLike('sku', $term)
                    ->groupEnd()
                    ->findAll();
    }
}
