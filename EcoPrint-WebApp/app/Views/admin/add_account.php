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
                    <button type="submit" class="btn btn-success" form="addAccountForm">Okay</button>
                </div>
            </div>
        </div>
    </div>
<div class="row">
    <div class="col-lg-12">
        <h5>Create Participating Office Account</h5>
        <div class="card">
            <div class="card-body">
                <form method="post" class="my-3 mx-5" id="addAccountForm" action="<?= base_url('admin/admin-and-office/add-account')?>">
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
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="office_name">Office Name</label>
                        <input type="text" class="form-control" id="office_name" name="office_name" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:  </label>
                        <input type="radio" id="role" name="role" value="admin" required> Admin
                        <input type="radio" id="role" name="role" value="print" required> Participating Office
                    </div>
                    <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#accountModal">Add</button>
                </form>
                <table class="table table-sm table-hover" id="suggestionTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="suggestionBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script {csp-script-nonce}>
document.getElementById('email').addEventListener('keyup', function() {
    let query = this.value;

    if (query.length < 2) {
        document.getElementById('suggestionTable').style.display = 'none';
        return;
    }

    fetch('<?= base_url()?>admin/admin-and-office/suggest-account?term=' + query)
        .then(res => res.json())
        .then(data => {
            let body = document.getElementById('suggestionBody');
            body.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(user => {
                    let row = `<tr>
                        <td>${user.first_name} ${user.last_name}</td>
                        <td>${user.email}</td>
                        <td><button type="button" class="btn btn-sm btn-primary select-email" data-email="${user.email}">Select</button></td>
                    </tr>`;
                    body.innerHTML += row;
                });
                document.getElementById('suggestionTable').style.display = 'table';
            } else {
                document.getElementById('suggestionTable').style.display = 'none';
            }

            // Attach click event for buttons
            document.querySelectorAll('.select-email').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('email').value = this.getAttribute('data-email');
                    document.getElementById('suggestionTable').style.display = 'none';
                });
            });
        });
});
</script>
<?= $this->endSection() ?>