<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="card mt-3">
    <div class="card-content">
        <div class="row row-group m-2">
            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $totalUsers ?> 
                        <span class="float-right"><i class="fa fa-users"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Total Students</p>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $totalRecycled ?> 
                        <span class="float-right"><i class="fa fa-recycle"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar bg-success"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Recycled Bottles</p>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $totalPrintJobs ?> 
                        <span class="float-right"><i class="fa fa-print"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar bg-warning"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Print Logs</p>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-3 border-light">
                <div class="card-body">
                    <h5 class="text-white mb-0"><?= $activeMachines ?> 
                        <span class="float-right"><i class="fa fa-recycle"></i></span>
                    </h5>
                    <div class="progress my-3">
                        <div class="progress-bar bg-warning"></div>
                    </div>
                    <p class="mb-0 text-white small-font">Active Machines</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8 col-xl-8">
        <div class="card">
            <div class="card-header">System Traffic
                <div class="card-action">
                    <div class="dropdown">
                        <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                        <i class="icon-options"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-inline">
                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-white"></i>Recycling</li>
                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-light"></i>Printing</li>
                </ul>
                <div class="chart-container-1">
                    <canvas id="chart1"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-xl-4">
        <div class="card">
            <div class="card-header">Weekly sales
                <div class="card-action">
                    <div class="dropdown">
                        <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                        <i class="icon-options"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="javascript:void();">Action</a>
                            <a class="dropdown-item" href="javascript:void();">Another action</a>
                            <a class="dropdown-item" href="javascript:void();">Something else here</a>
                            <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void();">Separated link</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-2">
                        <canvas id="chart2"></canvas>
                    </div>
                </div>
                    <div class="table-responsive">
                        <table class="table align-items-center" id="bottleTableBody">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  <!--End Row-->

<style {csp-style-nonce}>
.chart-container-1 {
    position: relative;
    min-height: 350px;
    height: 350px;
    margin-bottom: 2.5rem;
    z-index: 1;
    background: transparent;
    display: flex;
    align-items: flex-end;
}
.chart-container-2 {
    position: relative;
    /* height: 250px; increase height */
    max-height: 250px;
    overflow: visible;
}
#chart1 {
    width: 100% !important;
    height: 350px !important;
    display: block;
}
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}
.card-header select {
    margin-left: 1rem;
    min-width: 120px;
}
@media (max-width: 991px) {
    .chart-container-1 {
        min-height: 220px;
        height: 220px;
    }
    #chart1 {
        height: 220px !important;
    }
}

/* Prevent overlap with lower content */
.card .row-group {
    margin-top: 1.5rem !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>
<script {csp-script-nonce}>

// Normalize and group data
function processTrafficData(raw) {
    const labels = [];
    const recycling = {};
    const printing = {};

    raw.forEach(row => {
        // pick the right label field
        const label = row.date || row.week || row.month;

        if (!labels.includes(label)) labels.push(label);

        if (row.transaction_type === 'recycling') {
            recycling[label] = row.count;
        }
        if (row.transaction_type === 'printing') {
            printing[label] = row.count;
        }
    });

    // sort labels ascending
    labels.sort();

    return {
        labels,
        recycling: labels.map(l => recycling[l] || 0),
        printing: labels.map(l => printing[l] || 0)
    };
}

// PHP data
const trafficDaily   = <?= json_encode($traffic_daily ?? []) ?>;
const trafficWeekly  = <?= json_encode($traffic_weekly ?? []) ?>;
const trafficMonthly = <?= json_encode($traffic_monthly ?? []) ?>;

const dailyData   = processTrafficData(trafficDaily);
const weeklyData  = processTrafficData(trafficWeekly);
const monthlyData = processTrafficData(trafficMonthly);

let currentData = dailyData;

// Chart renderer
function renderChart(data) {
    const ctx = document.getElementById('chart1').getContext('2d');
    if (window.trafficChart) window.trafficChart.destroy();

    window.trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Recycling',
                    data: data.recycling,
                    borderColor: '#ffffff',                 // white line
                    backgroundColor: 'rgba(255,255,255,0.2)', // light white fill
                    fill: true,
                    lineTension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff'
                },
                {
                    label: 'Printing',
                    data: data.printing,
                    borderColor: '#5a6d80',                   // grey/blue line
                    backgroundColor: 'rgba(90,109,128,0.2)',  // translucent fill
                    fill: true,
                    lineTension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#5a6d80'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false   // 🔴 disables Chart.js legend
            },
            scales: {
                xAxes: [{
                    ticks: { fontColor: '#fff' },
                    gridLines: { color: 'rgba(255,255,255,0.1)' }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        fontColor: '#fff',
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;  // show only whole numbers
                            }
                        }
                    },
                    gridLines: {
                        display: true,
                        color: "rgba(255,255,255,0.1)"
                    },
                }]
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    renderChart(currentData);

    const dropdown = document.createElement('select');
    dropdown.innerHTML = `
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
    `;
    dropdown.className = 'form-control form-control-sm w-auto d-inline ml-2';

    dropdown.onchange = function() {
        renderChart(
            this.value === 'daily' ? dailyData :
            this.value === 'weekly' ? weeklyData :
            monthlyData
        );
    };

    // attach only to System Traffic card-header
    document.querySelector('.card-header').appendChild(dropdown);
});

// --- Chart 2: Most Recycled Bottle Types ---
// --- Chart 2: Most Recycled Bottle Types (FIXED) ---
(function() {
    var ctx = document.getElementById("chart2").getContext('2d');
    var myChart = null; // Store chart instance
    
    fetch('<?= base_url('admin/get-bottle-stats') ?>')
        .then(response => response.json())
        .then(data => {
            var labels = data.map(item => item.bottle_type);
            const counts = data.map(item => Number(item.total));
            
            myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        backgroundColor: [
                            "#ffffff",
                            "rgba(255, 255, 255, 0.70)",
                            "rgba(255, 255, 255, 0.50)",
                            "rgba(255, 255, 255, 0.30)"
                        ],
                        data: counts,
                        borderWidth: [0, 0, 0]
                    }]
                },
                options: {
                    responsive: true, // ✅ Ensure this is true
                    maintainAspectRatio: false,
                    legend: {
                        position: "bottom",
                        labels: {
                            fontColor: '#ddd',
                            boxWidth: 12,
                            padding: 15,
                            fontSize: 12
                        }
                    },
                    tooltips: {
                        displayColors: false
                    }
                }
            });
            
            // --- Table update ---
            const tbody = document.getElementById("bottleTableBody");
            tbody.innerHTML = "";
            const totalAll = counts.reduce((a, b) => a + b, 0);
            const colors = myChart.data.datasets[0].backgroundColor;

            console.log('Total of all bottles:', totalAll);
            
            data.forEach((item, index) => {
                const color = colors[index];
                const percentage = totalAll > 0 ? ((item.total / totalAll) * 100).toFixed(2) : 0;
                console.log('Percentage for', item.bottle_type, 'with: ', item.total, ':', percentage);
                const row = `<tr>
                    <td>${item.bottle_type}</td>
                    <td>${item.credit_value ?? 0}</td>
                    <td>${percentage}%</td>
                </tr>`;
                tbody.insertAdjacentHTML("beforeend", row);
            });
        })
        .catch(error => console.error('Error loading bottle stats:', error));
    
    // ✅ FIX: Handle window resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (myChart) {
                myChart.resize();
            }
        }, 50);
    });
})();

</script>


<?= $this->endSection() ?>



