<?php

namespace App\Models;

use CodeIgniter\Model;

class RfidConModel extends Model
{
    protected $table = 'rfid_con';
    protected $primaryKey = 'rfid_tag';
    protected $allowedFields = ['rfid_tag'];
    protected $returnType = 'array';
}
