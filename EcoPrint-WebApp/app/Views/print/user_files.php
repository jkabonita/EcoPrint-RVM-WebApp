<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="email" class="form-control" id="name" name="name" value="<?= esc($print_user['last_name']) . ', ' . esc($print_user['first_name'])?>" readonly>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= esc($print_user['email']) ?>" readonly>
        </div>
        <div class="form-group">
            <label for="credits">Current Credits</label>
            <input type="email" class="form-control" id="credits" name="credits" value="<?= esc($print_user['credit_balance']) ?>" readonly>
        </div>
        <div class="row px-3 justify-content-between align-items-center">
          <?php $success = session()->getFlashdata('success'); ?>
          <?php if ($success): ?>
              <script>
                  alert("<?= esc($success, 'js') ?>");
              </script>
          <?php endif; ?>
          <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
          <?php endif; ?>
        </div>  
        <div class="row p-2 justify-content-center align-items-center">
        </div>
			  <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                  <th>File Name</th>
                  <th>Uploaded At</th>
                  <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!empty($files)): ?>
              <?php foreach ($files as $file): ?>
              <tr>
                  <td><?= esc($file['original_filename']) ?></td>
                  <td><?= esc($file['uploaded_at']) ?></td>
                  <td>
                          <a href="<?= base_url('print/user-files/view/' . $file['file_id']) ?>" class="btn mx-3 btn-info">Print</a>
                  </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="8"><center>No files found in your account.</center></td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
