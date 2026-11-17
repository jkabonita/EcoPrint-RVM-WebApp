<?php

namespace App\Models;

use CodeIgniter\Model;

class CreditTransferModel extends Model
{
    protected $table = 'credit_transfers';
    protected $primaryKey = 'transfer_id';
    protected $allowedFields = ['sender_id', 'receiver_id', 'amount'];
    protected $returnType = 'array';

    public function getUserTransfers($userId) {
        return $this->db->table('credit_transfers')
            ->select('credit_transfers.*, 
                    sender.email AS sender_email, 
                    receiver.email AS receiver_email,
                    sender.first_name AS sender_fname,
                    receiver.first_name AS receiver_fname')
            ->join('users AS sender', 'sender.user_id = credit_transfers.sender_id')
            ->join('users AS receiver', 'receiver.user_id = credit_transfers.receiver_id')
            ->where('credit_transfers.sender_id', $userId)
            ->orWhere('credit_transfers.receiver_id', $userId)
            ->orderBy('credit_transfers.transfer_id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
