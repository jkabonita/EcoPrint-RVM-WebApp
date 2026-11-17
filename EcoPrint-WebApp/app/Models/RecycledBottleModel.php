<?php

namespace App\Models;

use CodeIgniter\Model;

class RecycledBottleModel extends Model
{
    protected $table = 'recycled_bottles';
    protected $primaryKey = 'recycle_bottle_id';
    protected $allowedFields = ['recycle_log_id', 'bottle_type_id', 'credit_value_at_recycle'];
    protected $returnType = 'array';
}
