<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title float-left">Printing Offices</h5>
          <a href="<?=base_url()?>print/print-auth" class="btn btn-success btn-icon-split float-right">Print</a>
        </div>
        <?php if($offices): ?>
            <?php foreach($offices as $key => $office): ?>
            <div class="card">
                <div class="card-body"> 
                    <h2 class="card-title"><span><i class="fa fa-print"></i></span> <?= ++$key ?></h2>
                    <h3 class="card-subtitle"><?= $office['office_name'] ?></h3>
                    <p class="card-text"><?= $office['email'] ?></p>
                    <p class="card-text">Print Jobs: <?= $office['completed_jobs_count'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>