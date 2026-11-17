<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="card mt-3">
    <div class="card-content">
        <div class="row row-group m-0">
            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $totalCredits ?> 
                        <span class="float-right"><i class="zmdi zmdi-money"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Total Credits</p>
                </div>
            </div>
            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $recycleCount ?> 
                        <span class="float-right"><i class="fa fa-recycle"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar bg-success"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Recycle Count</p>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $files ?> 
                        <span class="float-right"><i class="fa fa-print"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar bg-warning"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Printed Files</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Transaction History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Transaction Type</th>
                <th scope="col">Amount</th>
                <th scope="col">Balance After</th>
              </tr>
            </thead>
            <tbody>
            <?php if(!empty($transactions)): ?>
              <?php foreach($transactions as $transaction): ?>
              <tr>
                  <td><?= esc($transaction['transaction_type'])?></td>
                  <td><?= esc($transaction['amount'])?></td>
                  <td><?= esc($transaction['balance_after'])?></td>
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
<?= $this->endSection() ?>
	
