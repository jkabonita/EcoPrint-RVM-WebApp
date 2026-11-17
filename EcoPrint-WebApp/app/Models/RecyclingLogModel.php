<?php

namespace App\Models;

use CodeIgniter\Model;

class RecyclingLogModel extends Model
{
    protected $table = 'recycling_logs';
    protected $primaryKey = 'recycle_log_id';
    protected $allowedFields = ['user_id', 'log_timestamp'];
    protected $returnType = 'array';

   public function getRecyclingLogsWithTotals(int $userId)
    {
        return $this->select('recycling_logs.*, SUM(recycled_bottles.credit_value_at_recycle) as total_credits, COUNT(recycled_bottles.recycle_bottle_id) as total_bottles')
                    ->join('recycled_bottles', 'recycled_bottles.recycle_log_id = recycling_logs.recycle_log_id', 'left')
                    ->where('recycling_logs.user_id', $userId)
                    ->groupBy('recycling_logs.recycle_log_id')
                    ->orderBy('recycling_logs.recycle_log_id', 'DESC')
                    ->findAll(); // Use findAll() to get all results
    }

}

