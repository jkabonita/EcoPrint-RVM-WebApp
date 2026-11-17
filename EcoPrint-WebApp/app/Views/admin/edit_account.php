<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<!-- Modals -->
 <style {csp-style-nonce}>
    #icon {
      font-size: 5rem;
      color: #0087D7;
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
                        <i class="zmdi zmdi-info-outline" id="icon"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to elevate account's role?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn blue-button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" form="editAccountForm">Okay</button>
                </div>
            </div>
        </div>
    </div>
<div class="row">
    <div class="col-lg-12">
        <h5>Update Participating Office Account</h5>
        <div class="card">
            <div class="card-body">
                <form method="post" form_id="editAccountForm" class="my-3 mx-5" action="<?= base_url('admin/admin-and-office/edit-account/' . $account['user_id']) ?>">
                    <?= csrf_field() ?>
                    <?php $success = session()->getFlashdata('success'); ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success p-2"><?= esc($success) ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" id="email" name="email" value="<?= esc($account['email']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="office_name">Office Name</label>
                        <input type="text" class="form-control" id="office_name" name="office_name" value="<?= esc($account['office_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:  </label>
                        <input type="radio" id="role" name="role" value="admin" <?= $account['role'] === 'admin' ? 'checked' : '' ?> required> Admin
                        <input type="radio" id="role" name="role" value="print" <?= $account['role'] === 'print' ? 'checked' : '' ?> required> Participating Office
                    </div>
                    <button type="submit" class="btn btn-info btn-block">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>