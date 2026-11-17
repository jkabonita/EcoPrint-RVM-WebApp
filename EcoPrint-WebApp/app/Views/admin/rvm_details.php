<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="modal fade" id="imageViewModal" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageViewModalLabel">Error Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="errorImageDisplay" src="" class="img-fluid" alt="Error Image" style="max-height: 80vh; max-width: 100%;">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <h5 class="card-title mt-5">Error Logs</h5>
                    <table id="myTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Detail</th>
                                <th>Date & Time</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rvm_error_logs)): ?>
                            <?php foreach ($rvm_error_logs as $error): ?>
                            <tr>
                                <td><?= esc($error['error_type']) ?></td>
                                <td><?= esc($error['error_detail']) ?></td>
                                <td><?= esc($error['date_time']) ?></td>
                                <td>
                                    <button type="button"
                                            id="viewButton"
                                            class="btn btn-info view-image-btn"
                                            data-toggle="modal"
                                            data-target="#imageViewModal"
                                            data-image-name="<?= esc($error['error_image']) ?>"
                                            <?=($error['error_image'] === null) ? "disabled" : "" ?>>
                                        View
                                    </button>
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

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>

<script {csp-script-nonce}>
  document.addEventListener("DOMContentLoaded", function() {
    const viewButtons = document.querySelectorAll('#viewButton');
    const errorImageDisplay = document.getElementById('errorImageDisplay');

    viewButtons.forEach(button => {
      button.addEventListener('click', () => {
        const image_name = button.getAttribute('data-image-name');
        errorImageDisplay.setAttribute('src', `<?= base_url('public/uploads/error_images/') ?>${image_name}`);
      });
    });
  });
</script>

<?= $this->endSection() ?>