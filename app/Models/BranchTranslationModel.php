<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchTranslationModel extends Model
{
    protected $table         = 'branch_translations';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'branch_id',
        'language',
        'name',
        'description',
        'address',
    ];
}
