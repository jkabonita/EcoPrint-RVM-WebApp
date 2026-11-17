<?php

namespace App\Models;

use CodeIgniter\Model;

class MachineErrorLogsModel extends Model
{
    protected $table            = 'machine_error_logs';
    protected $primaryKey       = 'error_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['machine_id', 'error_type', 'error_detail', 'error_image'];
}
