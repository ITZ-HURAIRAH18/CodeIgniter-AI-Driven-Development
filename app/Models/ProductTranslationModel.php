<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductTranslationModel extends Model
{
    protected $table         = 'product_translations';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'product_id',
        'language',
        'name',
        'description',
    ];
}
