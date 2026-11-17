<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'rfid_tag', 'first_name', 'last_name', 'office_name',
        'email', 'username', 'password', 'role', 'credit_balance', 'theme_preference'
    ];
    protected $returnType = 'array';

    public function deductCredits($userId, $amount) {
        return $this->set('credit_balance', "credit_balance - $amount", false)
                    ->where('user_id', $userId)
                    ->update();
    }

    public function addCredits($userId, $amount) {
        return $this->set('credit_balance', "credit_balance + $amount", false)
                    ->where('user_id', $userId)
                    ->update();
    }
}
