<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<style {csp-style-nonce}>
   #icon-send {
     font-size: 5rem;
     color: #0087D7;
   }
   #icon-error {
     font-size: 5rem;
     color: #d70000ff;
   }
   .text-md-lg { /* Adjusted class name for clarity */
     font-size: 1.5rem;
   }
 </style>
    <div class="modal fade" id="sendCreditsModal" tabindex="-1" aria-labelledby="sendCreditsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-info">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center py-4">
                        <i class="zmdi zmdi-info-outline" id="icon-send"></i>
                        <p class="mt-3 mb-0 text-md-lg" id="transfer_message"></p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmSendButton" class="btn btn-info">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
          <div class="modal-body text-center py-4">
            <i class="zmdi zmdi-close-circle" id="icon-error"></i>
            <p class="mt-3 mb-0 text-md-lg text-dark" id="errorMessage"></p>
          </div>
          <div class="modal-footer border-top bg-light">
            <button type="button" class="btn btn-danger" data-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Current Credits: <?= esc(number_format($totalCredits ?? 0, 2)) ?></h5>
        <h5 class="card-title">Transfer Credits</h5>
        <form method="post" id="creditTransferForm" class="my-3 mx-5" action="<?= base_url('dashboard/credit-transfers') ?>">
          <?= csrf_field() ?>
          <?php $success = session()->getFlashdata('success'); ?>
          <?php if ($success): ?>
              <div class="alert alert-success p-2"><?= esc($success) ?></div>
          <?php endif; ?>
          <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
          <?php endif; ?>

          <div class="form-group">
              <label for="reciever" class="sr-only">Email</label>
              <div class="position-relative">
              <input type="email" name="reciever" id="reciever" class="form-control input-shadow" placeholder="Enter Receiver Email" required>
              </div>
          </div>

          <div class="form-group">
              <label for="amount" class="sr-only">Amount</label>
              <div class="position-relative">
              <input type="number" name="amount" id="amount" step="0.01" min="0.01" class="form-control input-shadow" placeholder="Enter Amount" required>
              </div>
          </div>
          <button type="button" class="btn btn-light btn-block" id="sendButton">Send</button>
        </form>
            <h5 class="card-title mt-5">Your Transaction History</h5>
        <div class="table-responsive">
          <table id="myTable" class="table table-striped">
            <thead>
              <tr>
                <th>Type</th>
                <th>Email</th>
                <th>Date</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!empty($transfers) && is_array($transfers)): ?>
              <?php foreach ($transfers as $log): ?>
                <?php
                  $isSender = $log['sender_id'] == ($user_id ?? -1); // Check against $user_id, use a safe fallback
                  $type = $isSender ? 'Sent' : 'Received';
                  $email = $isSender ? $log['receiver_email'] : $log['sender_email'];
                  $amount = $isSender ? '- ' . number_format($log['amount'], 2) : '+ ' . number_format($log['amount'], 2);
                  $badgeClass = $isSender ? 'bg-danger' : 'bg-success';
                ?>
                <tr>
                  <td><span class="badge <?= $badgeClass ?> p-2"><?= $type ?></span></td>
                  <td><?= esc($email) ?></td>
                  <td><?= esc(date('Y-m-d H:i:s', strtotime($log['created_at'] ?? 'now'))) ?></td>
                  <td class="<?= $isSender ? 'text-danger' : 'text-success' ?>">
                      <?= $amount ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5"><center>No transferred credits found for your account.</center></td>
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
  document.addEventListener('DOMContentLoaded', function () {
    const sendButton = document.getElementById('sendButton');
    const form = document.getElementById('creditTransferForm');
    const recieverInput = document.getElementById('reciever');
    const amountInput = document.getElementById('amount');
    const messageElement = document.getElementById('transfer_message');
    const confirmSendButton = document.getElementById('confirmSendButton');
    const errorMessage = document.getElementById('errorMessage');

    let receiverUserData = null;

    // Helper: show error modal
    function showError(message) {
      errorMessage.innerHTML = message;
      $('#errorModal').modal('show');
    }

    function appendTransferMessage(fname, lname, email, amount) {
      messageElement.innerHTML = `
        Are you sure you want to send <strong>${amount.toFixed(2)}</strong> credits to:<br>
        <strong>${fname} ${lname}</strong><br>
        (${email})
      `;
    }

    sendButton.addEventListener('click', async function () {
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const recieverEmail = recieverInput.value.trim();
      const amount = parseFloat(amountInput.value);

      if (amount <= 0) {
        showError("The amount must be greater than zero.");
        return;
      }

      try {
        const response = await fetch('<?= base_url('api/user/get-user') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
          },
          body: JSON.stringify({ recieverEmail })
        });

        const data = await response.json();
        console.log('API response:', data);

        if (data.status === 'success' && data.user) {
          receiverUserData = data.user;

          const fname = receiverUserData.first_name;
          const lname = receiverUserData.last_name;
          const email = receiverUserData.email;


          console.log('Receiver found:', fname, lname, email);

          appendTransferMessage(fname, lname, email, amount);

          $('#sendCreditsModal').modal('show');

        } else {
          showError(data.message || 'User not found for that email address.');
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
        showError('An unexpected error occurred while checking the user.');
      }
    });

    confirmSendButton.addEventListener('click', function () {
      $('#sendCreditsModal').modal('hide');
      form.submit();
    });
  });
</script>

<?= $this->endSection() ?>