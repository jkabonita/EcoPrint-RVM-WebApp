<div class="container mt-4">
    <h4>Printing: <?= esc($file['original_filename']) ?></h4>
    <div class="mb-3">
        <a href="<?= base_url('admin/user-files/finish-printing/') . $file['file_id'] ?>" class="btn btn-secondary">Finish</a>
    </div>
    <?php $success = session()->getFlashdata('success'); ?>
                    <?php if ($success): ?>
                        <div class="alert alert-info p-2"><?= $success ?></div>
                    <?php endif; ?>
    <?php
        $filePath = base_url('public/uploads/documents/' . $file['filename']);
        $ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
    ?>

    <?php if (strtolower($ext) === 'pdf'): ?>
        <iframe src="<?= $filePath ?>" width="100%" height="800px"></iframe>
    <?php else: ?>
        <p>Preview not supported. <a href="<?= $filePath ?>" target="_blank">Download or open in new tab</a></p>
    <?php endif; ?>
</div>