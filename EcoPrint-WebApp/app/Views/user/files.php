<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<!-- Modals -->
 <style {csp-style-nonce}>
    #icon-account {
      font-size: 5rem;
      color: #d70000ff;
    }
    .text-md {
      font-size: 1.5rem;
    }
  </style>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="zmdi zmdi-alert-triangle" id="icon-account"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to delete this file?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    <a href="" id="modalDeleteLink" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title float-left">My Files</h5>
            <a href="<?= base_url('dashboard/files/upload-file') ?>" class="btn btn-success btn-icon-split float-right">Upload</a>
          </div>
          <?php $success = session()->getFlashdata('success'); ?>
          <?php if ($success): ?>
              <div class="alert alert-success p-2"><?= esc($success) ?></div>
          <?php endif; ?>
          <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
          <?php endif; ?>
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
                      <?php if($file['status'] == 'Active'): ?>
                        <button type="button" class="btn btn-danger" id="deleteButton" data-toggle="modal" data-target="#deleteModal" data-id="<?= $file['file_id'] ?>">Delete</button>
                        <a href="<?= site_url('dashboard/files/view-file/' . $file['file_id']) ?>" class="btn mx-3 btn-info">View</a>
                      <?php endif;?>
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
<script {csp-script-nonce}>
  document.addEventListener("DOMContentLoaded", function() {
    const deleteButtons = document.querySelectorAll('#deleteButton');
    const modalDeleteLink = document.getElementById('modalDeleteLink');

    deleteButtons.forEach(button => {
      button.addEventListener('click', () => {
        const fileId = button.getAttribute('data-id');
        modalDeleteLink.setAttribute('href', `<?= base_url('dashboard/files/delete-file/') ?>${fileId}`);
      });
    });
  });
</script>
<?= $this->endSection() ?>