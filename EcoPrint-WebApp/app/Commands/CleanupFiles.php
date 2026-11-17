<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PrintJobModel;
use App\Models\FileModel;

class CleanupFiles extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Maintenance';
    
        /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'cleanup:files';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Deletes expired files after scheduled delete_at time.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $printJobModel = new PrintJobModel();
        $fileModel = new FileModel();

        $expiredFiles = $fileModel
        ->where('deleted_at <=', date('Y-m-d H:i:s'))
        ->findAll();

            foreach ($expiredFiles as $file) {
                if (file_exists(WRITEPATH . 'uploads/' . $file['filename'])) {
                    unlink(WRITEPATH . 'uploads/' . $file['filename']);
                    CLI::write("Deleted file: " . $file['filename'], 'green');
                }

                // Remove DB record so it won’t run again
                $fileModel->delete($file['file_id']);
        }
        CLI::write("Cleanup completed.", 'green');
    }
}   
