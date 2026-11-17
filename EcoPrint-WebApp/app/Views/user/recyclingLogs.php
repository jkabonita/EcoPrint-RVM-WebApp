<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">My Recycling History</h5>
			  <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th>Date/Time</th>
                <th>Total Bottles</th>
                <th>Total Credits Gained</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!empty($recyclingLogs) && is_array($recyclingLogs)): ?>
              <?php foreach ($recyclingLogs as $log): ?>
              <tr>
                  <td><?= esc($log['log_timestamp']) ?></td>
                  <td><?= esc($log['total_bottles']) ?></td>
                  <td><?= esc(number_format($log['total_credits'], 2)) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4"><center>No recycling logs found for your account. Start recycling today! ♻️</center></td>
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