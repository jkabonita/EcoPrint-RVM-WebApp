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

    .blue-button {
      background-color: #0087D7;
      color: white;
    }
  </style>
    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="zmdi zmdi-alert-triangle" id="icon-account"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to remove account's elevated role?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn blue-button" data-dismiss="modal">Cancel</button>
                    <a href="" id="modalRemoveLink" class="btn btn-danger">Remove</a>
                </div>
            </div>
        </div>
    </div>

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title float-left">Accounts</h5>
          <a href="<?=base_url()?>admin/admin-and-office/add-account" class="btn btn-info btn-icon-split float-right">Add</a>
        </div>
        <?php $success = session()->getFlashdata('success'); ?>
        <?php if ($success): ?>
                <div class="alert alert-success p-2"><?= esc($success) ?></div>
            </script>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>    
			  <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th scope="col">User Email</th>
                <th scope="col">Office Name</th>
                <th scope="col">Role</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if(!empty($accounts)): ?>
              <?php foreach($accounts as $account): ?>
              <tr>
                  <td><?= esc($account['email'])?></td>
                  <td><?= esc($account['office_name'])?></td>
                  <td><?= $account['role'] === "admin" ? "Admin" : "Participating Office" ?></td>
                  <td>
                    <a href="<?= base_url('admin/admin-and-office/edit-account/' . $account['user_id']) ?>" class="btn btn-warning">Edit</a>
                    <?php if($account['role'] !== 'admin'): ?>
                      <button type="button" class="btn btn-danger" id="removeButton" data-toggle="modal" data-target="#accountModal" data-id="<?= $account['user_id'] ?>">Remove</button>
                    <?php endif; ?>
                  </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="6"><center>No data found.</center></td>
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
    const removeButtons = document.querySelectorAll('#removeButton');
    const modalRemoveLink = document.getElementById('modalRemoveLink');

    removeButtons.forEach(button => {
      button.addEventListener('click', () => {
        const userId = button.getAttribute('data-id');
        modalRemoveLink.setAttribute('href', `<?= base_url('admin/admin-and-office/remove-privilege/') ?>${userId}`);
      });
    });
  });
</script>
<?= $this->endSection() ?>