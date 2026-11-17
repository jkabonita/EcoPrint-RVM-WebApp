<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<!-- Modals -->
 <style {csp-style-nonce}>
    #icon-account {
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
    <div class="modal fade" id="bottleModal" tabindex="-1" aria-labelledby="bottleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="zmdi zmdi-info-outline" id="icon-account"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to update bottle's credit value?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn blue-button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" form="editBottleForm">Update</button>
                </div>
            </div>
        </div>
    </div>
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Bottle Types</h5>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-info p-2"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>  
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>  
            <div class="table-responsive">
            <table id="myTable" class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">Bottle ID</th>
                    <th scope="col">Bottle Name Type</th>
                    <th scope="col">Credit Value</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($bottles)): ?>
                    <?php foreach($bottles as $bottle): ?>
                    <tr>
                        <td><?= esc($bottle['bottle_type_id'])?></td>
                        <td><?= esc($bottle['bottle_type'])?></td>
                        <td>
                            <form action="<?= base_url('admin/recyclable-bottles/edit-credit/') . $bottle['bottle_type_id']?>" id="editBottleForm<?= $bottle['bottle_type_id'] ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="number" class="form-control" name="credit_value" step="0.01" value="<?= esc($bottle['credit_value']) ?>">
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-info btn-block btn-update" 
                                    data-toggle="modal" 
                                    data-target="#bottleModal" 
                                    data-form="editBottleForm<?= $bottle['bottle_type_id'] ?>">
                                Update
                            </button>
                        </td>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No data found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
        <h5 class="card-title">Credits Update History</h5>
            <div class="table-responsive">
                <table id="myTable" class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">History ID</th>
                        <th scope="col">Bottle ID</th>
                        <th scope="col">Credit Value</th>
                        <th scope="col">Effective Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($histories)): ?>
                    <?php foreach($histories as $history): ?>
                    <tr>
                        <td><?= esc($history['history_id'])?></td>
                        <td><?= esc($history['bottle_type_id'])?></td>
                        <td><?= esc($history['credit_value'])?></td>
                        <td><?= esc($history['effective_date'])?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No data found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script {csp-script-nonce}>
    document.addEventListener("DOMContentLoaded", function() {
      const updateButtons = document.querySelectorAll('.btn-update');
      const modalConfirmButton = document.querySelector('#bottleModal .btn-success');
    
      updateButtons.forEach(button => {
        button.addEventListener('click', function() {
          const formId = this.getAttribute('data-form');
          // Link modal confirm button to the correct form
          modalConfirmButton.setAttribute('form', formId);
        });
      });
    });
</script>


<?= $this->endSection() ?>