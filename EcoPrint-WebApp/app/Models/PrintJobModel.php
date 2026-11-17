<?php

namespace App\Models;

use CodeIgniter\Model;

class PrintJobModel extends Model
{
    protected $table = 'print_jobs';
    protected $primaryKey = 'job_id';
    protected $allowedFields = [
        'user_id',
        'file_id',
        'officer_id',
        'paper_size',
        'number_of_copies',
        'page_ranges',
        'total_pages_printed',
        'total_amount',
        'status',
        'printed_at',
    ];
    protected $returnType = 'array';
}
