<div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title float-left">Print Logs</h5>
                <a href="<?=base_url()?>admin/print-auth" class="btn btn-success btn btn-success btn-icon-split float-right">Print</a>
            </div>
              
			  <div class="table-responsive">
               <table id="myTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">Job ID</th>
                      <th scope="col">Officer ID</th>
                      <th scope="col">Number of Pages</th>
                      <th scope="col">Total Cost</th>
                      <th scope="col">Date</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if(!empty($printJobs)): ?>
                    <?php foreach($printJobs as $printJob): ?>
                    <tr>
                        <td><?= esc($printJob['job_id'])?></td>
                        <td><?= esc($printJob['officer_id'])?></td>
                        <td><?= esc($printJob['pages_printed'])?></td>
                        <td><?= esc($printJob['total_amount'])?></td>
                        <td><?= esc($printJob['printed_at'])?></td>
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