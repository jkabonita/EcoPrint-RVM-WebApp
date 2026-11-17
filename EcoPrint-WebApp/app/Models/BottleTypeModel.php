<?php

namespace App\Models;

use CodeIgniter\Model;

class BottleTypeModel extends Model
{
    protected $table = 'bottle_types';
    protected $primaryKey = 'bottle_type_id';
    protected $allowedFields = ['bottle_type', 'credit_value'];
    protected $returnType = 'array';
}
