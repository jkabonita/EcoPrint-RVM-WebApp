<?php

namespace App\Models;

use CodeIgniter\Model;

class PrintingPricesModel extends Model
{
    protected $table            = 'printing_prices';
    protected $primaryKey       = 'printing_price_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['variant_name', 'price'];
}
