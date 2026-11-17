<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
      <h5 class="card-title">Recycling Logs</h5>
			  <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th scope="col">User ID</th>
                <th scope="col">Timestamp</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if(!empty($recyclingLogs)): ?>
              <?php foreach($recyclingLogs as $recyclingLog): ?>
              <tr>
                  <td><?= esc($recyclingLog['user_id'])?></td>
                  <td><?= esc($recyclingLog['log_timestamp'])?></td>
                  <td>
                      <a type="button" href="<?= base_url() . 'admin/edit/edit/' . $recyclingLog['recycle_log_id']?>" class="btn btn-warning">More Details</a>
                  </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4"><center>No data found.</center></td>
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