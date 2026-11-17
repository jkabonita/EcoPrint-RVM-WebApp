<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4>Printing: <?= esc($file['original_filename']) ?></h4>
                <div class="mb-3">
                    <a href="<?= base_url($role . '/user-files/cancel/') . $job['job_id'] ?>" class="btn btn-secondary">Cancel</a>
                </div>
                <form method="post" class="my-3 mx-5" action="<?= base_url($role . '/user-files/print/') . $job['job_id'] ?>">
                    <?= csrf_field() ?>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-secondary p-2"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="numPagesInput">Number of Pages</label>
                        <input type="number" class="form-control" id="numPagesInput" name="numPages" value="<?= $jobs['pages_printed'] ?>">
                        <label for="amount">Amount Per Page</label>
                        <input type="number" class="form-control" id="amount" name="amount">
                    </div>
                    <button type="submit" class="btn btn-light btn-block">Confirm Printing</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
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
</div>