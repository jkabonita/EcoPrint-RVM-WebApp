<?php

namespace App\Models;

use CodeIgniter\Model;

class RVMachineModel extends Model
{
    protected $table            = 'rv_machines';
    protected $primaryKey       = 'machine_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['machine_name','ip_address', 'location', 'cpu_temp', 'status', 'last_heartbeat'];
}

