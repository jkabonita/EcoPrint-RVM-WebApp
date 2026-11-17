<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\BottleTypeModel;
use App\Models\RecyclingLogModel;
use App\Models\RecycledBottleModel;
use App\Models\FileModel;
use App\Models\BottleTypeCreditHistoryModel;
use App\Models\PrintJobModel;
use App\Models\UserCreditHistoryModel;
use App\Models\RVMachineModel;
use App\Models\MachineErrorLogsModel;

class Admin extends BaseController
{
    private $userModel;
    private $bottleTypeModel;
    private $recyclingLogModel;
    private $recycledBottleModel;
    private $fileModel;
    private $bottleTypeCreditHistoryModel;
    private $printJobModel;
    private $userCreditHistoryModel;
    private $rVMachineModel;
    private $machineErrorLogsModel;
    private $admin_id;
    private $session;
    private $role;

    public function __construct(){
        $this->session = session();
        $this->admin_id = $this->session->get('user_id');
        $this->userModel = new UserModel();
        $this->bottleTypeModel = new BottleTypeModel();
        $this->recyclingLogModel = new RecyclingLogModel();
        $this->recycledBottleModel = new RecycledBottleModel();
        $this->fileModel = new FileModel();
        $this->bottleTypeCreditHistoryModel = new BottleTypeCreditHistoryModel();
        $this->printJobModel = new PrintJobModel();
        $this->userCreditHistoryModel = new UserCreditHistoryModel();
        $this->rVMachineModel = new RVMachineModel();
        $this->machineErrorLogsModel = new MachineErrorLogsModel();
        $this->role = $this->session->get('role');
    }
    public function index()
    {
        
        $data = [
            'totalUsers' => $this->userModel->countAllResults(),
            'totalRecycled' => $this->recyclingLogModel->countAllResults(),
            'totalPrintJobs' => $this->printJobModel->where('status', 'Completed')->countAllResults(),
            'activeMachines' => $this->rVMachineModel->where('status', 'running')->orWhere('status', 'in_use')->countAllResults(),
            'role' => $this->role,
        ];

        // Add traffic data for graphs
        $data['traffic_daily'] = $this->getTrafficData('daily');
        $data['traffic_weekly'] = $this->getTrafficData('weekly');
        $data['traffic_monthly'] = $this->getTrafficData('monthly');

        return view('admin/dashboard', $data);
    }

    /**
     * Get traffic data for graphs (recycling & printing)
     * @param string $period daily|weekly|monthly
     * @return array
     */
    private function getTrafficData($period = 'daily') {
        $builder = $this->userCreditHistoryModel;
        $select = "DATE(created_at) as date, transaction_type, COUNT(*) as count";
        $groupBy = "DATE(created_at), transaction_type";
        $where = [];
        $limit = 30;
        if ($period === 'daily') {
            $select = "DAY(created_at) as date, transaction_type, COUNT(*) as count";
            $groupBy = "DAY(created_at), transaction_type";
            $limit = 30;
        } elseif ($period === 'weekly') {
            $select = "YEARWEEK(created_at, 1) as week, transaction_type, COUNT(*) as count";
            $groupBy = "YEARWEEK(created_at, 1), transaction_type";
            $limit = 12;
        } elseif ($period === 'monthly') {
            $select = "DATE_FORMAT(created_at, '%b %Y') as month, transaction_type, COUNT(*) as count";
            $groupBy = "DATE_FORMAT(created_at, '%b %Y'), transaction_type";
            $limit = 12;
        }

        $builder->select($select)
            ->whereIn('transaction_type', ['recycling', 'printing'])
            ->orderBy('MAX(created_at)', 'DESC')
            ->groupBy($groupBy)
            ->limit($limit);
        $result = $builder->findAll();
        return $result;
    }

    public function getBottleRecycleStats()
    {
        $builder = $this->recycledBottleModel;
        $builder->select('bt.bottle_type, bt.credit_value, COUNT(recycled_bottles.bottle_type_id) as total');
        $builder->join('bottle_types bt', 'bt.bottle_type_id = recycled_bottles.bottle_type_id');
        $builder->groupBy('recycled_bottles.bottle_type_id');
        $query = $builder->get();

        return $this->response->setJSON($query->getResult());
    }

    public function listOfUsers()
    {
        $data = [
            'users' => $this->userModel->where('role', 'user')->findAll(),
            'role' => $this->role,
        ];

        return view('admin/users_list', $data);
    }

    public function reverseVendingMachines()
    {
        $data =[
            'machines' => $this->rVMachineModel->findAll(),
            'role' => $this->role,
        ];

        return view('admin/reverse_vending_machines', $data);
    }
    
    public function reverseVendingMachineDetails($rvm_id)
    {
        $rvm['role'] = $this->role;
        $rvm['rvm_details'] = $this->rVMachineModel->find($rvm_id);
        
        if(!$rvm['rvm_details']) {
            return redirect()->back()->with('error', 'Invalid Machine ID.');
        }
        
        $rvm['rvm_error_logs'] = $this->machineErrorLogsModel->where('machine_id', $rvm_id)->orderBy('error_id', 'DESC')->findAll();
        
        return view('admin/rvm_details', $rvm);
    }
    
    public function machineErrorView($error_id)
    {
        $error_log = $this->machineErrorLogsModel->find($error_id);
        
        if(!$error_log) {
            return redirect()->back()->with('error', 'Cannot find error log.');
        }
        
        return view('admin/rvm_error', $error_log);
    }

    public function printingService()
    {
        $completedPrintJobs = $this->printJobModel
            ->builder()
            ->select('COUNT(*)')
            ->where('officer_id = users.user_id')
            ->where('status', 'Completed')
            ->getCompiledSelect();
        
        $printOfficeWithJobCount = $this->userModel
            ->select('users.*')
            ->select("($completedPrintJobs) as completed_jobs_count")
            ->where('role', 'print')
            ->orWhere('role', 'admin')
            ->orderBy('completed_jobs_count', 'DESC')
            ->findAll();

        $data =[
            'offices' => $printOfficeWithJobCount,
            'role' => $this->role,
        ];

        return view('admin/printing_service', $data);
    }

    public function recyclableBottles()
    {
        $data =[
            'bottles' => $this->bottleTypeModel->findAll(),
            'histories' => $this->bottleTypeCreditHistoryModel->findAll(),
            'role' => $this->role,
        ];

        return view('admin/recyclable_bottles', $data);
    }

    public function editBottleCredit($bottle_type_id)
    {
        $newPrice = $this->request->getPost('credit_value');
        $bottleType = $this->bottleTypeModel->find($bottle_type_id);

        if($bottleType['credit_value'] == $newPrice) {
            return redirect()->back()->with('error', 'Cannot update with the same current credit value.');
        }

        $updateData = [
            'credit_value' => $newPrice
        ];

        $logData = [
            'bottle_type_id' => $bottle_type_id,
            'credit_value' => $newPrice
        ];

        $db = $this->bottleTypeCreditHistoryModel->db;
        $db->transBegin();

        $update = $this->bottleTypeModel->update($bottle_type_id, $updateData);
        $log = $this->bottleTypeCreditHistoryModel->insert($logData);

        if($update && $log) {
            $db->transCommit();
            return redirect()->back()->with('success', 'Credit value updated successfully.');
        } else {
            $db->transRollback();
            return redirect()->back()->with('error', "Unable to update bottle's credit value, please try again later.");
        }
    }

    public function recyclingLogs()
    {
        $data = [
            'recyclingLogs' => $this->recyclingLogModel->findAll(),
            'role' => $this->role,
        ];

        return view('admin/recycling_logs', $data);
    }

    public function accounts()
    {
        $data['offices'] = $this->userModel->where('role', 'print')->findAll();
        $data['admins'] = $this->userModel->where('role', 'admin')->findAll(); 
        $data['accounts'] = array_merge($data['admins'], $data['offices']);
        $data['role'] = $this->role;

        return view('admin/accounts', $data);
    }

    public function addAccountPage()
    {
        $data['role'] = $this->role;
        return view('admin/add_account', $data);
    }

    public function suggestAccount()
    {
        $term = $this->request->getGet('term');

        if (!$term) {
            return $this->response->setJSON([]);
        }

        $users = $this->userModel
        ->like('email', $term)
        ->orLike('first_name', $term)
        ->orLike('last_name', $term)
        ->findAll(10);

        $results = array_map(function($u) {
        return [
            'email' => $u['email'],
            'first_name' => $u['first_name'],
            'last_name' => $u['last_name'],
        ];
        }, $users);

        return $this->response->setJSON($results);
    }

    public function addAccount()
    {
        $officeData = $this->request->getPost(['email', 'office_name', 'role']);

        $checkRole = $this->userModel->where('email', $officeData['email'])->first();

        if (!$checkRole) {
        return redirect()->back()->with('error', 'The email does not exist.');
        }
        if ($checkRole['role'] === $officeData['role']) {
            return redirect()->back()->with('error', 'This email is already assigned to the same role.');
        }

        $checkOfficeName = $this->userModel
                            ->builder()
                            ->where("LOWER(office_name) =", strtolower($officeData['office_name']))
                            ->get()
                            ->getResult();

        if($checkOfficeName) {
            return redirect()->back()->with('error', 'Office Name already exists.');
        }

        if($this->userModel->update($checkRole['user_id'], ['role' => $officeData['role'], 'office_name' => $officeData['office_name']])) {
            return redirect()->to('admin/admin-and-office')->with('success', 'Account updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Error creating account, please try again later.');
        }
    }

    public function editAccountPage($user_id)
    {
        $account = $this->userModel->find($user_id);

        if (!$account) {
            return redirect()->to('admin/admin-and-office')->with('error', 'Account not found.');
        }

        $data['account'] = $account;
        $data['role'] = $this->role;

        return view('admin/edit_account', $data);
    }

    public function editAccount($user_id)
    {
        $officeData = $this->request->getPost(['email', 'office_name', 'role']);
        
        if(!$officeData['office_name']) {
          return redirect()->back()->with('error', 'Don\'t leave the inputs empty.');
        }

        $checkRole = $this->userModel->where('email', $officeData['email'])->first();

        if (!$checkRole) {
            return redirect()->back()->with('error', 'The email does not exist.');
        }
        if ($checkRole['role'] === $officeData['role'] && $checkRole['office_name'] === $officeData['office_name']) {
            return redirect()->back()->with('error', 'This email is already assigned to the same role and office name.');
        }

        $checkOfficeName = $this->userModel
                            ->builder()
                            ->where("LOWER(office_name) =", strtolower($officeData['office_name']))
                            ->get()
                            ->getResult();

        if($checkOfficeName && $checkOfficeName[0]->user_id != $user_id) {
            return redirect()->back()->with('error', 'Office Name already exists.');
        }

        if($this->userModel->update($checkRole['user_id'], ['role' => $officeData['role'], 'office_name' => $officeData['office_name']])) {
            return redirect()->to('admin/admin-and-office')->with('success', 'Account updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Error creating account, please try again later.');
        }
    }

    public function removePrivilege($user_id)
    {
        $user = $this->userModel->find($user_id);

        if(!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        if($user['role'] === 'admin') {
            return redirect()->back()->with('error', 'Cannot remove privileges from an admin account.');
        }

        if($this->userModel->update($user_id, ['role' => 'user', 'office_name' => null])) {
            return redirect()->to('admin/admin-and-office')->with('success', 'Account privileges removed successfully.');
        } else {
            return redirect()->back()->with('error', 'Error removing account privileges, please try again later.');
        }
    }

}
