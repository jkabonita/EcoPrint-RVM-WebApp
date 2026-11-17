<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\PricingService;
use App\Models\UserModel;
use App\Models\FileModel;
use App\Models\PrintJobModel;
use App\Models\UserCreditHistoryModel;

class PrintController extends BaseController
{
    private $userModel;
    private $fileModel;
    private $printJobModel;
    private $userCreditHistoryModel;
    private $office_id;
    private $role;
    private $session;
    private $pricingService;

    public function __construct()
    {
        $this->session = session();
        $this->office_id = $this->session->get('user_id');
        $this->pricingService = new PricingService();
        $this->role = $this->session->get('role');
        $this->userModel = new UserModel();
        $this->fileModel = new FileModel();
        $this->printJobModel = new PrintJobModel();
        $this->userCreditHistoryModel = new UserCreditHistoryModel();
    }

    public function printLogs()
    {
        if($this->role === 'admin'){
            $data = [
                'printJobs' => $this->printJobModel->findAll(),
                'role' => $this->role
            ];
        } else if($this->role === 'print'){
            $data = [
                'printJobs' => $this->printJobModel
                                ->where('officer_id', $this->office_id)
                                ->findAll(),
                'role' => $this->role
            ];
        } else {
            return redirect()->to('/auth')->with('error', 'Access Denied.');
        }

        return view('print/print_logs', $data);
    }

    public function printAuth()
    {
        $method = strtolower($this->request->getMethod());
        $data = [
            'role' => $this->role,
        ];

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
                if(($this->role === 'admin') || ($this->role === 'print')){
                    return redirect()->to('print/user-files');
                } else{
                    return redirect()->to('/auth')->with('error', 'Access Denied.');
                }
            }
        }
        
        if(($this->role === 'admin') || ($this->role === 'print')){
            return view('print/print_auth', $data);
        } else{
            return redirect()->to('/auth')->with('error', 'Access Denied.');
        }
    }

    public function userFiles()
    {
        $print_user_id = $this->session->get('print_user_id');
        $data['print_user'] = $this->userModel->find($print_user_id);
        $data['files'] = $this->fileModel->getActiveFilesByUser($print_user_id);
        $data['role'] = $this->role;

        if(($this->role === 'admin') || ($this->role === 'print')){
            return view('print/user_files', $data);
        } else{
            return redirect()->to('/auth')->with('error', 'Access Denied.');
        }
    }

    public function initiatePrint($fileId)
    {
        $print_user_id = $this->session->get('print_user_id');
        $file = $this->fileModel->find($fileId);
        if (!$file || $file['status'] !== 'Active') {
            return redirect()->back()->with('error', 'File not found or already deleted.');
        }

        $filePath = ROOTPATH . 'public/uploads/documents/'. $file['filename'];
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found on server.');
        }

        $fileUrl = base_url('public/uploads/documents/' . $file['filename']);

        // Auto-detect pages
        $pdfResult = $this->pricingService->analyzePdf($filePath, $file['filename'], $this->office_id);

        // Create a pending print job (without credit_per_page)
        $jobData = [
            'user_id' => $print_user_id,
            'file_id' => $fileId,
            'officer_id' => $this->office_id,
            'paper_size' => $pdfResult['pages'][0]['size'],
            'total_pages_printed' => $pdfResult['pageCount'],
            'total_amount' => $pdfResult['total'],
            'status' => 'Pending'
        ];

        $jobId = $this->printJobModel->insert($jobData);

        $data = [
            'file' => $file,
            'role' => $this->role,
            'file_id' => $fileId,
            'officer_id' => $this->office_id,
            'pages_printed' => $pdfResult['pageCount'],
            'total_amount' => $pdfResult['total'],
            'status' => 'Printing',
            'pdfResult' => $pdfResult,
            'fileUrl' => $fileUrl,
            'jobId' => $jobId
        ];
        

        if(($this->role === 'admin') || ($this->role === 'print')){
            return view('print/view_user_file', $data);
        } else{
            return redirect()->to('/auth')->with('error', 'Access Denied.');
        }
    }

    public function processPrint($jobId)
    {
        $officerId = session()->get('user_id');
        $copies = $this->request->getPost('numCopies');
        $range = $this->request->getPost('pageRange');
        $printedPages = $this->request->getPost('numPages');
        $amount = $this->request->getPost('amount');

         // Validate inputs
        if ($copies <= 0 || $printedPages <= 0 || $amount <= 0) {
            return redirect()->back()->with('error', 'Invalid print parameter submitted.');
        }

        $job = $this->printJobModel->find($jobId);
        if (!$job) {
            return redirect()->back()->with('error', 'Print job not found.');
        }

        $print_user = $this->userModel->find($job['user_id']);

        if( !$print_user) {
            return redirect()->back()->with('error', 'User not found.');
        }
        if ($print_user['credit_balance'] < $amount) {
            return redirect()->back()->with('error', 'Insufficient credits.');
        }


        if ($copies <= 0 || $printedPages <= 0 || $amount <= 0) {
            return redirect()->back()->with('error', 'Invalid print parameter submitted.');
        }

        try {
            // Update print job details
            $this->printJobModel->update($jobId, [
                'number_of_copies' => $copies,
                'page_ranges' => $range,
                'total_pages_printed' => $printedPages,
                'total_amount' => $amount,
                'status' => 'Pending'
            ]);

            $uri = "ecoprint://{$jobId}/{$officerId}";
    
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Print job initiated.',
                'uri' => $uri // <--- Pass the URI back to JavaScript
            ]);

        } catch (\Exception $e) {
            // ... (error handling)
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update print job: ' . $e->getMessage()
            ])->setStatusCode(500); // Use an appropriate error status code
        }
    }


    public function getPrintJob($jobId, $officerId)
    {
        $job = $this->printJobModel
                ->where('job_id', $jobId)
                ->where('officer_id', $officerId)
                ->where('status', 'Pending')
                ->first();

        if (!$job) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Print job not found or already processed.']);
        }
        
        $file = $this->fileModel->find($job['file_id']);

        if ($file) {
            $job['filename'] = $file['filename']; // e.g. 1727602933_myfile.pdf
            // optionally original name if you store it
            $job['original_name'] = $file['original_name'] ?? null;
        } else {
            $job['filename'] = null;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'job' => $job
        ]);
    }

    public function updatePrintJobStatus($jobId)
    {
        $data = $this->request->getJSON(true);
        
        $updateStatus = $this->printJobModel->update($jobId, [
            'status' => $data['status'],
            'printed_at' => ($data['status'] === 'Completed') ? date('Y-m-d H:i:s') : null
        ]);

        if (!$updateStatus) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Failed to update print job status.']);
        }

        if($data['status'] === 'Completed'){
            try{
                $job = $this->printJobModel->find($jobId);
                $file = $this->fileModel->find($job['file_id']);
                $print_user = $this->userModel->find($job['user_id']);

                if (empty($file['deleted_at'])) {
                    $this->fileModel->update($file['file_id'], [
                        'deleted_at' => date('Y-m-d H:i:s', strtotime('+15 days')) // or +15 days
                    ]);
                }

                $transaction = [
                    'user_id' => $print_user['user_id'],
                    'transaction_type' => 'printing',
                    'total_amount' => $job['total_amount'],
                    'balance_after' => $print_user['credit_balance'] - $job['total_amount'],
                ];

                // Insert printing transaction into user_credit_history
                $this->userCreditHistoryModel->insert([
                    'user_id' => $transaction['user_id'],
                    'transaction_type' => 'printing',
                    'amount' => $transaction['total_amount'],
                    'balance_after' => $transaction['balance_after'],
                    'reference_id' => $jobId,
                    'reference_table' => 'print_jobs',
                ]);

                $this->userModel->deductCredits($transaction['user_id'], $transaction['total_amount']);
            } catch (\Throwable $e) {
                // Log the error but do not fail the entire request
                log_message('error', 'Failed to process post-print actions: ' . $e->getMessage());
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Failed to complete print job transaction. ' . $e->getMessage()]);
            }
           
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Print job updated successfully'
        ]);
    }

    public function getJobStatus($jobId)
    {
        $job = $this->printJobModel->find($jobId);

        if (!$job) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Print job not found.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'job_status' => $job['status']
        ]);
    }
}
