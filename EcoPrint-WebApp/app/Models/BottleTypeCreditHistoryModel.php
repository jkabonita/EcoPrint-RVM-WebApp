<?php

namespace App\Models;

use CodeIgniter\Model;

class BottleTypeCreditHistoryModel extends Model
{
    protected $table            = 'bottle_type_credit_history';
    protected $primaryKey       = 'history_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['bottle_type_id', 'credit_value', 'effective_date'];
}
