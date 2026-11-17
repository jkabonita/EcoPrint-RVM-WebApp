<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">List of Users</h5>
        <form method="post" class="my-3 mx-5">
            <?= csrf_field() ?>
            <?php $success = session()->getFlashdata('success'); ?>
            <?php if ($success): ?>
                <div class="alert alert-success p-2"><?= esc($success) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Search</label>
                <input type="text" class="form-control" id="email" name="email" required>
            </div>
        </form>
        <div class="table-responsive">
        <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
              </tr>
            </thead>
            <tbody>
            <?php if(!empty($users)): ?>
              <?php foreach($users as $user): ?>
              <tr>
                  <td><?= esc($user['first_name'])?></td>
                  <td><?= esc($user['last_name'])?></td>
                  <td><?= esc($user['email'])?></td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4">No data found.</td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <table class="table table-sm table-hover" id="suggestionTable" style="display:none;">
              <thead>
                  <tr>
                      <th scope="col">First Name</th>
                      <th scope="col">Last Name</th>
                      <th scope="col">Email</th>
                  </tr>
              </thead>
              <tbody id="suggestionBody">
              </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script {csp-script-nonce}>
document.getElementById('email').addEventListener('keyup', function() {
    let query = this.value;

    if (query.length < 2) {
        document.getElementById('suggestionTable').style.display = 'none';
        document.getElementById('myTable').style.display = 'table';
        return;
    } else {
        document.getElementById('suggestionTable').style.display = 'table';
        document.getElementById('myTable').style.display = 'none';
    }

    fetch('<?= base_url()?>admin/admin-and-office/suggest-account?term=' + query)
        .then(res => res.json())
        .then(data => {
            let body = document.getElementById('suggestionBody');
            body.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(user => {
                    let row = `<tr>
                        <td>${user.first_name}</td>
                        <td>${user.last_name}</td>
                        <td>${user.email}</td>
                    </tr>`;
                    body.innerHTML += row;
                });
                document.getElementById('suggestionTable').style.display = 'table';
                document.getElementById('myTable').style.display = 'none';
            } else {
                let row = `<tr>
                        <td colspan="3">No data found.</td>
                    </tr>`;
                body.innerHTML += row;
                document.getElementById('suggestionTable').style.display = 'table';
                document.getElementById('myTable').style.display = 'none';
            }
        });
});
</script>
<?= $this->endSection() ?>