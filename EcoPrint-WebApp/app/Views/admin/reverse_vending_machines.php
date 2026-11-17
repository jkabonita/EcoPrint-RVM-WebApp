<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<style {csp-style-nonce}>
    .status-running {
        color: green;
    }
    .status-in_use {
        color: #0087D7;
    }
    .status-down {
        color: red;
    }
    .status-error {
        color: orange;
    }
</style>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="fa fa-exclamation-circle down-icon" id="icon"></i>
                        <p class="mt-3 mb-0" id="message">The machine is currently down.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12" id="machine-list-container">
        <p class="text-center text-muted">Loading machine data...</p>
        
    </div>
</div>

<script {csp-script-nonce}>
    function renderMachineList(machines) {
    const container = document.getElementById('machine-list-container');
    if (!container) return;

    let html = '';

    if (machines && machines.length > 0) {
        machines.forEach(machine => {
            const status_class = machine.status.toLowerCase();
            const status_text = machine.status.replace(/_/g, ' ').toUpperCase();

            let status_icon = '';
            switch (status_class) {
                case 'running': status_icon = 'fa-check-circle'; break;
                case 'in_use': status_icon = 'fa fa-recycle'; break;
                case 'error': status_icon = 'fa-exclamation-triangle'; break;
                case 'down': status_icon = 'fa-times-circle'; break;
                default: status_icon = 'fa-question-circle';
            }

            html += `
            <div class="card machine-card">
                <div class="card-header">
                    <h2 class="card-title">${machine.machine_name}</h2>
                    <span class="status-badge status-${status_class}">
                        <i class="fa ${status_icon}"></i> ${status_text}
                    </span>
                </div>
                <div class="card-body">
                    <div class="detail-group">
                        <p class="detail-label">IP Address</p>
                        <h5 class="detail-value">${machine.ip_address}</h5>
                    </div>
                    <div class="detail-group">
                        <p class="detail-label">Location</p>
                        <h5 class="detail-value">${machine.location}</h5>
                    </div>
                    <div class="detail-group">
                        <p class="detail-label">CPU Temp</p>
                        <h5 class="detail-value">${machine.cpu_temp} &#8451;</h5>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('admin/reverse-vending-machine-details/') ?>${machine.machine_id}" class="btn btn-info">View Errorrs</a>
                </div>
            </div>`;
        });
    } else {
        html = '<p class="text-center text-danger">No machines found.</p>';
    }

    container.innerHTML = html;
}

function pollMachineList() {
    const url = '<?= base_url('api/machine/get-status') ?>';
    console.log('Fetching machine data from:', url);

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            renderMachineList(data);
            console.log('Machine data updated successfully.');
        })
        .catch(error => console.error('Error fetching machine data:', error));
}

pollMachineList();
setInterval(pollMachineList, 2000);

</script>

<?= $this->endSection() ?>