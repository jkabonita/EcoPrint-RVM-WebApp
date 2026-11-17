<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table            = 'files';
    protected $primaryKey       = 'file_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['user_id', 'filename', 'original_filename', 'status', 'deleted_at'];

    public function getActiveFilesByUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'Active')
                    ->findAll();
    }

    public function softDelete($fileId)
    {
        return $this->update($fileId, ['status' => 'Deleted', 'deleted_at' => date('Y-m-d H:i:s')]);
    }
}
