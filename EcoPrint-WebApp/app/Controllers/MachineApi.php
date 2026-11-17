<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\BottleTypeModel;
use App\Models\RecyclingLogModel;
use App\Models\RecycledBottleModel;
use App\Models\UserCreditHistoryModel;
use App\Models\RVMachineModel;
use App\Models\MachineErrorLogsModel;
use App\Models\RfidConModel;

class MachineApi extends ResourceController
{
    protected $format = 'json';
    public $userModel;
    public $bottleTypeModel;
    public $recyclingLogModel;
    public $recycledBottleModel;
    public $userCreditHistoryModel;
    public $rVMachineModel;
    public $machineErrorLogsModel;
    public $rfidConModel;

    public function __construct()
    {
        $this->recyclingLogModel = new RecyclingLogModel();
        $this->recycledBottleModel = new RecycledBottleModel();
        $this->userCreditHistoryModel = new UserCreditHistoryModel();
        $this->userModel = new UserModel();
        $this->bottleTypeModel = new BottleTypeModel();
        $this->rVMachineModel = new RVMachineModel();
        $this->machineErrorLogsModel = new MachineErrorLogsModel();
        $this->rfidConModel = new RfidConModel();
    }

    public function index()
    {
        return $this->respond(['message' => 'Machine API is running.']);
    }

    public function sessionInit($rfid)
    {

        $user = $this->userModel->where('rfid_tag', $rfid)->first();

        if (!$user) {
            $check_rfid = $this->rfidConModel->where('rfid_tag', $rfid)->first();
            if ($check_rfid) {
                return $this->respond([
                    'status' => 'info',
                    'url' => base_url('/register/' . $rfid)
                ]);
            } else {
                $createRfidCon = $this->rfidConModel->insert(['rfid_tag' => $rfid]);
                $checkRfid = $this->rfidConModel->where('rfid_tag', $rfid)->first();

                if(!$checkRfid){
                    return $this->failServerError('Failed to create RFID connection.');
                }

                return $this->respond([
                        'status' => 'info',
                        'url' => base_url('/register/' . $rfid)
                    ]);
            }
        }

        $bottles = $this->bottleTypeModel->findAll();

        return $this->respond([
            'status' => 'success',
            'user' => $user,
            'bottle_types' => $bottles
        ]);
    }

    public function finalizeSession()
    {
        $input = $this->request->getJSON(true); // Get JSON as associative array
        
        $user_id = $input['user_id'] ?? null;
        $bottles = $input['bottles'] ?? null;

        if (!$user_id || !is_array($bottles) || count($bottles) === 0) {
            return $this->failValidationErrors("User ID and bottles array are required.");
        }

        foreach ($bottles as $bottle_type_id) {
            if (!is_numeric($bottle_type_id) || (int)$bottle_type_id <= 0 || (int)$bottle_type_id > 3) {
                return $this->failValidationErrors("Invalid bottle type ID: " . htmlspecialchars($bottle_type_id));
            }
        }
        
        // Use a database transaction for data integrity
        $db = $this->recyclingLogModel->db;
        $db->transBegin();

        try {
            
            // Get all bottle types to avoid N+1 query problem
            $allBottleTypes = $this->bottleTypeModel->findAll();
            $bottleTypeCredits = array_column($allBottleTypes, 'credit_value', 'bottle_type_id');

            // Create recycling log
            $recycle_log_id = $this->recyclingLogModel->insert(['user_id' => $user_id]);

            $totalCredits = 0;
            
            foreach ($bottles as $bottle_type_id) {
                $bottle_type_id = (int)$bottle_type_id; // Sanitize input
                if (isset($bottleTypeCredits[$bottle_type_id])) {
                    $credit_value = $bottleTypeCredits[$bottle_type_id];
                    $this->recycledBottleModel->insert([
                        'recycle_log_id' => $recycle_log_id,
                        'bottle_type_id' => $bottle_type_id,
                        'credit_value_at_recycle' => $credit_value,
                    ]);
                    $totalCredits += $credit_value;
                }
            }

            // Update user balance
            $user = $this->userModel->find($user_id);
            if (!$user) {
                $db->transRollback();
                return $this->failNotFound("User not found.");
            }
            
            $newBalance = $user['credit_balance'] + $totalCredits;
            $this->userModel->update($user_id, ['credit_balance' => $newBalance]);
            
            // Log credit history
            $this->userCreditHistoryModel->insert([
                'user_id' => $user['user_id'],
                'transaction_type' => 'recycling',
                'amount' => $totalCredits,
                'balance_after' => $newBalance,
                'reference_id' => $recycle_log_id,
                'reference_table' => 'recycling_logs',
            ]);
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->failServerError('Failed to finalize session. Database transaction failed.');
            } else {
                $db->transCommit();
                return $this->respond([
                    'message' => 'Recycling session saved',
                    'total_earned' => $totalCredits,
                    'new_balance' => $newBalance
                ]);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->failServerError('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function heartbeat($machine_id)
    {
        log_message('debug', 'Heartbeat machine_id: ' . print_r($machine_id, true));

        $json = $this->request->getJSON(true); // ✅ getJSON(true) returns associative array

        $status     = $json['status'] ?? 'down';
        $ip_address = $json['ip_address'] ?? null;
        $cpu_temp   = $json['cpu_temp'] ?? null;

        // ✅ Check if machine exists
        $machine = $this->rVMachineModel->find($machine_id);
        if (!$machine) {
            return $this->failNotFound("Machine not found.");
        }

        // ✅ Update record safely
        $updateData = [
            'last_heartbeat' => date('Y-m-d H:i:s'),
            'ip_address'     => $ip_address,
            'cpu_temp'       => $cpu_temp,
            'status'         => $status,
        ];

        if ($this->rVMachineModel->update($machine_id, $updateData)) {
            return $this->respond([
                'message' => 'Heartbeat received.',
                'data' => $updateData
            ]);
        }

        return $this->fail('Failed to update heartbeat.');
    }

    public function reportError($machine_id)
    {
        // Find the machine
        $machine = $this->rVMachineModel->find($machine_id);
        if (!$machine) {
            return $this->failNotFound("Machine not found.");
        }
    
        // Get POST fields (form-data)
        $error_type = $this->request->getPost('error_type');
        $error_detail = $this->request->getPost('error_detail');
        $error_image = $this->request->getFile('error_image');
    
        // Log incoming data for debugging
        log_message('error', 'POST DATA: ' . json_encode(['error_type' => $error_type, 'error_detail' => $error_detail]));
        log_message('error', 'FILE DATA: ' . print_r($error_image, true));
    
        // Validate required fields
        if (!$error_type || !$error_detail) {
            return $this->failValidationErrors('Missing required POST fields: error_type or error_detail.');
        }
    
        // Handle file upload (optional)
        $newName = null;
        if ($error_image && $error_image->isValid() && !$error_image->hasMoved()) {
            try {
                $newName = $error_image->getRandomName();
                $error_image->move(ROOTPATH . 'public/uploads/error_images', $newName);
            } catch (\Exception $e) {
                return $this->failServerError('File upload failed: ' . $e->getMessage());
            }
        }
    
        // Prepare error log data
        $error_log = [
            'machine_id'   => $machine_id,
            'error_type'   => $error_type,
            'error_detail' => $error_detail,
            'error_image'  => $newName,
        ];
    
        // Database transaction
        $db = $this->rVMachineModel->db;
        $db->transBegin();
    
        try {
            // Update machine status
            $this->rVMachineModel->update($machine_id, [
                'last_heartbeat' => date('Y-m-d H:i:s'),
                'status' => 'error'
            ]);
    
            // Insert error log
            $this->machineErrorLogsModel->insert($error_log);
    
            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->failServerError('Database transaction failed.');
            } else {
                $db->transCommit();
                return $this->respond(['message' => 'Error reported successfully.']);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->failServerError('Unexpected error: ' . $e->getMessage());
        }
    }


    public function reportFullStorage($machine_id)
    {
        $machine = $this->rVMachineModel->find($machine_id);
        if (!$machine) {
            return $this->failNotFound("Machine not found.");
        }

        $db = $this->rVMachineModel->db;
        $db->transBegin();

        try {
            $this->rVMachineModel->update($machine_id, ['last_heartbeat' => date('Y-m-d H:i:s'), 'status' => 'full']);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->failServerError('Failed to report full storage. Database transaction failed.');
            } else {
                $db->transCommit();
                return $this->respond(['message' => 'Full storage reported successfully.']);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->failServerError('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function getMachineData()
    {
        $machines = $this->rVMachineModel->findAll();
        foreach ($machines as &$machine) {
            if (empty($machine['last_heartbeat'])) {
                continue;
            }

            $last_heartbeat = strtotime($machine['last_heartbeat']);
            $current_time = time();
            if ($last_heartbeat === false) {
                $machine['status'] = 'error'; 
                continue;
            }
            $time_diff = $current_time - $last_heartbeat;
            if($time_diff > 80) {
                $machine['status'] = 'down';
                $this->rVMachineModel->update($machine['machine_id'], ['status' => 'down']);
            }
        }

        unset($machine);
        return $this->response->setJSON($machines);
    }


}
