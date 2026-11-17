<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\FileModel;

class ParticipatingOffice extends BaseController
{
    private $userModel;
    private $fileModel;
    private $office_id;
    private $session;

    public function __construct()
    {
        $this->session = session();
        $this->office_id = $this->session->get('user_id');
        $this->userModel = new UserModel();
        $this->fileModel = new FileModel();
    }

    public function index()
    {
        $data['office'] = $this->userModel->find($this->office_id);
        $data['printCount'] = $this->fileModel->where('officer_id', $this->office_id)->countAllResults();

        return view('print/template/header') . view('print/dashboard', $data) . view('print/template/footer');
    }
    public function updateProfile()
    {
        $office = $this->userModel->find($this->office_id);

        if (!$office) {
            return redirect()->back()->with('error', 'Office account not found.');
        }

        $username = $this->request->getPost('username');
        $newUsername = $this->request->getPost('newUsername');
        $password = $this->request->getPost('password');
        $currPassword = $this->request->getPost('currPassword');
        $newPassword = $this->request->getPost('newPassword');
        $conPassword = $this->request->getPost('conPassword');

        $updateData = [];

        // Handle setting or changing username
        if (is_null($office['username']) && !empty($username)) {
            // Check if the username is already in use
            if ($this->userModel->where('username', $username)->where('user_id !=', $this->office_id)->first()) {
                return redirect()->back()->with('error', 'Username is already in use.');
            }
            $updateData['username'] = $username;
        } elseif (!is_null($office['username']) && !empty($newUsername)) {
            // Check if new username is in use
            if ($this->userModel->where('username', $newUsername)->where('user_id !=', $this->office_id)->first()) {
                return redirect()->back()->with('error', 'New username is already taken.');
            }
            $updateData['username'] = $newUsername;
        }

        // Handle password change
        if (is_null($office['password']) && !empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif (!is_null($office['password']) && !empty($currPassword) && !empty($newPassword) && !empty($conPassword)) {
            if (!password_verify($currPassword, $office['password'])) {
                return redirect()->back()->with('error', 'Current password is incorrect.');
            }

            if ($newPassword != $conPassword) {
                return redirect()->back()->with('error', 'New password and confirmation do not match.');
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (!empty($updateData)) {
            $this->userModel->update($this->office_id, $updateData);
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }

        return redirect()->back()->with('error', 'Nothing was updated.');
    }

    public function printLogs()
    {
        $data = [
            'printJobs' => $this->fileModel
                            ->where('status', 'Printed')
                            ->where('officer_id', $this->office_id)
                            ->findAll()
        ];

        return view('print/template/header') . view('print/print_logs', $data) . view('print/template/footer');
    }

    public function printAuth()
    {
        $method = strtolower($this->request->getMethod());

        if($method == 'post'){
            $rfid = $this->request->getPost(['uid']);
            if($rfid == null){
              return redirect()->back()->with('error', 'RFID input is empty.');  
            }

            $user = $this->userModel->where('rfid_tag', $rfid)->first();

            if(!$user){
                return redirect()->back()->with('error', 'User not found.');
            }else{
                $this->session->set('print_user_id', $user['user_id']);
                //return redirect()->back()->with('success', 'User: '. $user['last_name'] . ', ' . $user['first_name']);
                return redirect()->to('print/user-files');
            }
        }

        return view('print/template/header') . view('print/print_auth') . view('print/template/footer');
    }

    public function userFiles()
    {
        $print_user_id = $this->session->get('print_user_id');
        $data['print_user'] = $this->userModel->find($print_user_id);
        $data['files'] = $this->fileModel->getUserFiles($print_user_id, "Not Printed");

        return view('print/template/header') . view('print/user_files', $data) . view('print/template/footer');
    }

    public function viewUserFile($file_id)
    {
        $file = $this->fileModel->find($file_id);

        if (!$file) {
            return redirect()->back()->with('error', 'File not found.');
        }

        $data['file'] = $file;
        return view('print/template/header')
            . view('print/view_user_file', $data)
            . view('print/template/footer');
    }

    public function printUserFile($file_id)
    {
        $print_user = $this->userModel->find($this->session->get('print_user_id'));
        $pageCount = $this->request->getPost('numPages');
        $totalAmount = $this->request->getPost('numPages') * $this->request->getPost('amount');

        if($pageCount <= 0) {
            return redirect()->back()->with('error', 'Invalid count of pages.');
        }

        if($totalAmount > $print_user['credit_balance']) {
            return redirect()->back()->with('error', "Credits insufficient.");
        }

        $printData = [
            'officer_id' => $this->office_id,
            'printed_at' => date('Y-m-d H:i:s'),
            'number_of_pages' => $pageCount,
            'amount' => $totalAmount,
            'status' => 'Printed',
        ];

        $db = $this->fileModel->db;
        $db->transBegin();

        $updateFile = $this->fileModel->update($file_id, $printData);
        $deductCredits = $this->userModel->deductCredits($print_user['user_id'], $totalAmount);

        if($updateFile && $deductCredits) {
            $db->transCommit();
            return redirect()->to('print/user-files/print-view/' . $file_id)->with('success', 'Clear to proceed to printing document.');
        } else{
            $db->transRollback();
            return redirect()->back()->with('error', 'Server error, please try again later');
        }
    }

    public function printView($file_id)
    {
        $file = $this->fileModel->find($file_id);

        if (!$file) {
            return redirect()->back()->with('error', 'File not found.');
        }

        $data['file'] = $file;
        return view('print/template/header')
            . view('print/print_view', $data)
            . view('print/template/footer'); 
    }

    public function finishPrinting($file_id)
    {
        $file = $this->fileModel->find($file_id);

        if (!$file) {
            return redirect()->to('print/user-files')->with('error', 'File not found.');
        }

        $filePath = ROOTPATH . 'public/uploads/documents/' . $file['filename'];

        if (file_exists($filePath)) {
            unlink($filePath);
            return redirect()->to('print/user-files')->with('success', 'Printing successful, file deleted.');
        }
    }


}
