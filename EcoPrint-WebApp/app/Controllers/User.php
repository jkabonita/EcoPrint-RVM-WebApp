<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\UserModel;
use App\Models\RecyclingLogModel;
use App\Models\RecycledBottleModel;
use App\Models\FileModel;
use App\Models\CreditTransferModel;
use App\Models\UserCreditHistoryModel;

class User extends BaseController
{
    private $user_id;
    private $role;
    private $session;
    private $userModel;
    private $recyclingLogModel;
    private $recycledBottleModel;
    private $fileModel;
    private $creditTransferModel;
    private $userCreditHistoryModel;

    public function __construct(){
        $this->session = session();
        $this->user_id = $this->session->get('user_id');
        $this->role = $this->session->get('role');
        $this->userModel = new UserModel();
        $this->recyclingLogModel = new RecyclingLogModel();
        $this->recycledBottleModel = new RecycledBottleModel();
        $this->fileModel = new FileModel();
        $this->creditTransferModel = new CreditTransferModel();
        $this->userCreditHistoryModel = new UserCreditHistoryModel();
    }

    public function index()
    {
        if (!$this->user_id) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $user = $this->userModel->find($this->user_id);
        $data['user'] = $user;
        $data['role'] = $this->role;
        $data['totalCredits'] = $user ? $user['credit_balance'] : 0;
        $data['recycleCount'] = $this->recyclingLogModel->where('user_id', $this->user_id)->countAllResults();
        $data['files'] = $this->fileModel->where('user_id', $this->user_id,)->where('status', 'Printed')->countAllResults();
        $data['transactions'] = $this->userCreditHistoryModel->where('user_id', $this->user_id)->orderBy('credit_history_id', 'DESC')->findAll(10);

        return view('user/dashboard', $data);
    }

    public function updateProfile()
    {
        $user = $this->userModel->find($this->user_id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $username = $this->request->getPost('username');
        $newUsername = $this->request->getPost('newUsername');
        $password = $this->request->getPost('password');
        $currPassword = $this->request->getPost('currPassword');
        $newPassword = $this->request->getPost('newPassword');
        $conPassword = $this->request->getPost('conPassword');

        $updateData = [];

        // Handle setting or changing username
        if (is_null($user['username']) && !empty($username)) {
            // Check if the username is already in use
            if ($this->userModel->where('username', $username)->where('user_id !=', $this->user_id)->first()) {
                return redirect()->back()->with('error', 'Username is already in use.');
            }
            $updateData['username'] = $username;
        } elseif (!is_null($user['username']) && !empty($newUsername)) {
            // Check if new username is in use
            if ($this->userModel->where('username', $newUsername)->where('user_id !=', $this->user_id)->first()) {
                return redirect()->back()->with('error', 'New username is already taken.');
            }
            $updateData['username'] = $newUsername;
        }

        // Handle password change
        if (is_null($user['password']) && !empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif (!is_null($user['password']) && !empty($currPassword) && !empty($newPassword) && !empty($conPassword)) {
            if (!password_verify($currPassword, $user['password'])) {
                return redirect()->back()->with('error', 'Current password is incorrect.');
            }

            if ($newPassword != $conPassword) {
                return redirect()->back()->with('error', 'New password and confirmation do not match.');
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (!empty($updateData)) {
            $this->userModel->update($this->user_id, $updateData);
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }

        return redirect()->back()->with('error', 'Nothing was updated.');
    }

    public function recyclingLogs(){

        $data['recyclingLogs'] = $this->recyclingLogModel->getRecyclingLogsWithTotals($this->user_id);
        $data['role'] = $this->role;
        return view('user/recyclingLogs', $data);
    }

    public function files(){

        $data['files'] = $this->fileModel->getActiveFilesByUser($this->user_id);
        $data['role'] = $this->role;

        return view('user/files', $data);
    }

    public function uploadFilePage(){
        return view('user/uploadFile', $data=['role' => $this->role]);
    }

    public function uploadFile(){

        $file = $this->request->getFile('document');

        if (! $this->validate([
            'document' => [
                'label' => 'Document',
                'rules' => 'uploaded[document]|mime_in[document,application/pdf]|max_size[document,2048]',
                'errors' => [
                    'uploaded' => 'Please select a file to upload.',
                    'mime_in'  => 'Only PDF files are allowed.',
                    'max_size' => 'The file size must not exceed 2MB.'
                ]
            ]
        ])) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('document'));
        }


        if ($file && $file->isValid() && !$file->hasMoved()) {
            $originalFilename = $file->getName(); // Get the original filename (e.g., MyDocument.pdf)
            $uniqueFilename = $file->getRandomName(); // Generate a unique name (e.g., abcd1234.pdf)

            // Move the file to the desired directory
            // Ensure the 'uploads' directory exists and is writable within your public folder
            $uploadPath = ROOTPATH . 'public/uploads/documents/';
            $file->move($uploadPath, $uniqueFilename);

            // --- 3. Database Insertion ---
            $fileData = [
                'user_id'         => $this->user_id,
                'filename'        => $uniqueFilename,        // Stored unique name
                'original_filename' => $originalFilename,
            ];

            if ($this->fileModel->insert($fileData)) {
                return redirect()->to('dashboard/files')->with('success', 'File uploaded successfully!');
            } else {
                // If DB insert fails, consider deleting the uploaded file
                unlink($uploadPath . $uniqueFilename);
                return redirect()->back()->with('error', 'Failed to save file information to database.');
            }
        } else {
            // Should be caught by validation, but as a fallback
            return redirect()->back()->with('error', 'File upload failed. Please try again.');
        }
    
    }

    public function viewFilePage($file_id)
    {
        $file = $this->fileModel->find($file_id);

        if (!$file) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Check user ownership
        if ($file['user_id'] !== $this->user_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $data['file'] = $file;
        $data['role'] = $this->role;
        return view('user/viewFile', $data);
    }

    public function deleteFile($file_id)
    {
        // Fetch the file record
        $file = $this->fileModel->find($file_id);

        if (!$file) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Ensure the logged-in user is the owner of the file
        if ($file['user_id'] != $this->user_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Construct the file path
        $filePath = ROOTPATH . 'public/uploads/documents/' . $file['filename'];

        // Attempt to delete the file from the directory if it exists
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the actual file
        }

        // Delete the database record
        if ($this->fileModel->delete($file_id)) {
            return redirect()->to('dashboard/files')->with('success', 'File deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete file from database.');
        }
    }
    
    public function creditTransfers() 
    {
        $method = strtolower($this->request->getMethod());

        if (!in_array($method, ['get', 'post'])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $user = $this->userModel->where('user_id', $this->user_id)->first();
        $data['totalCredits'] = $user ? $user['credit_balance'] : 0;
        $data['user_id'] = $this->user_id;

        if ($method === 'post') {
            return $this->processCreditTransferPost($user);
        }

        $data['transfers'] = $this->creditTransferModel->getUserTransfers($this->user_id);
        $data['role'] = $this->role;
        return view('user/creditTransfers', $data);
    }

    public function getUserToTransfer() 
    {
        $recieverEmail = $this->request->getJsonVar('recieverEmail');

        $reciver = $this->userModel->where('email', $recieverEmail)->first();

        if(!$reciver) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Recipient not found.']);
        }

        return $this->response->setJSON(['status' => 'success', 'user' => [
            'first_name' => $reciver['first_name'],
            'last_name' => $reciver['last_name'],
            'email' => $reciver['email']
        ]]);
    }

    private function processCreditTransferPost($sender) 
    {
        if (!$sender) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $postData = $this->request->getPost(['reciever', 'amount']);

        // Input Validation
        if (!filter_var($postData['reciever'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid recipient email format.');
        }

        $amount = (float) $postData['amount'];
        if (!is_numeric($amount) || $amount <= 0) {
            return redirect()->back()->with('error', 'Invalid credit amount.');
        }
        
        // Find Recipient
        $receiver = $this->userModel->where('email', $postData['reciever'])->first();
        if (!$receiver) {
            return redirect()->back()->with('error', 'Recipient was not found.');
        }
        
        // Pre-Transfer Checks
        if ($receiver['user_id'] == $this->user_id) {
            return redirect()->back()->with('error', 'Cannot transfer credits to yourself.');
        }
        if ($sender['credit_balance'] < $amount) {
            return redirect()->back()->with('error', 'Credits insufficient.');
        }

        $db = $this->userModel->db;
        $db->transBegin();

        try {
            // Step 1: Deduct from sender and add to receiver
            $this->userModel->deductCredits($this->user_id, $amount);
            $this->userModel->addCredits($receiver['user_id'], $amount);

            // Step 2: Log the transfer
            $transferData = [
                'sender_id'   => $this->user_id,
                'receiver_id' => $receiver['user_id'],
                'amount'      => $amount,
            ];
            $transferId = $this->creditTransferModel->insert($transferData);

            // Step 3: Fetch updated balances for accurate transaction history
            $updatedSender = $this->userModel->find($this->user_id);
            $updatedReceiver = $this->userModel->find($receiver['user_id']);

            if (!$updatedSender || !$updatedReceiver) {
                throw new \Exception('Failed to fetch updated user balances.');
            }

            // Step 4: Log transaction history for both users
            $senderTransaction = [
                'user_id'          => $this->user_id,
                'transaction_type' => 'transfer_out',
                'amount'           => $amount,
                'balance_after'    => $updatedSender['credit_balance'],
                'reference_id'     => $transferId,
                'reference_table'  => 'credit_transfers'
            ];

            $receiverTransaction = [
                'user_id'          => $receiver['user_id'],
                'transaction_type' => 'transfer_in',
                'amount'           => $amount,
                'balance_after'    => $updatedReceiver['credit_balance'],
                'reference_id'     => $transferId,
                'reference_table'  => 'credit_transfers'
            ];

            $this->userCreditHistoryModel->insert($senderTransaction);
            $this->userCreditHistoryModel->insert($receiverTransaction);

            // All good, commit the transaction
            $db->transCommit();
            return redirect()->back()->with('success', 'Credits transferred successfully to ' . $receiver['email'] . '.');

        } catch (\Exception $e) {
            // Something went wrong, rollback and log the error
            $db->transRollback();
            // Log the exception message for debugging, e.g., using a logger
            log_message('error', 'Credit transfer failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to transfer the credits, please try again later. If the issue persists, please contact us.');
        }
    }
}
