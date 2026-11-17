<?php

namespace App\Models;

use CodeIgniter\Model;

class UserCreditHistoryModel extends Model
{
    protected $table            = 'user_credit_history';
    protected $primaryKey       = 'credit_history_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'transaction_type', 'amount', 'balance_after', 'reference_id', 'reference_table'];
}
