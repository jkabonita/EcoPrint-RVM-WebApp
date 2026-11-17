<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <meta name="description" content=""/>
  <meta name="author" content=""/>
  <title>EcoPrint-Dashboard</title>
  <!-- loader -->
  <link href="<?=base_url()?>bootstrap/assets/css/pace.min.css" rel="stylesheet"/>
  <script src="<?=base_url()?>bootstrap/assets/js/pace.min.js"></script> 
  <!--favicon-->
  <link rel="icon" href="<?=base_url()?>bootstrap/assets/images/eco2.ico" type="image/x-icon">
  <!-- simplebar CSS-->
  <link href="<?=base_url()?>bootstrap/assets/plugins/simplebar/css/simplebar.css" rel="stylesheet"/>
  <!-- Bootstrap core CSS-->
  <link href="<?=base_url()?>bootstrap/assets/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- animate CSS-->
  <link href="<?=base_url()?>bootstrap/assets/css/animate.css" rel="stylesheet" type="text/css"/>
  <!-- Icons CSS-->
  <link href="<?=base_url()?>bootstrap/assets/css/icons.css" rel="stylesheet" type="text/css"/>
  <link href="<?=base_url()?>bootstrap/assets/css/ic.css" rel="stylesheet" type="text/css"/>
  <!-- Sidebar CSS-->
  <link href="<?=base_url()?>bootstrap/assets/css/sidebar-menu.css" rel="stylesheet"/>
  <!-- Custom Style-->
  <link href="<?=base_url()?>bootstrap/assets/css/app-style.css" rel="stylesheet"/>
  
  
  
</head>

<body class="bg-theme bg-theme1">
 
<!-- Start wrapper-->
 <div id="wrapper">
 
  <!--Start sidebar-wrapper-->
   <div id="sidebar-wrapper" data-simplebar="" data-simplebar-auto-hide="true">
     <div class="brand-logo">
      <a href="<?=base_url()?>dashboard">
       <img src="<?=base_url()?>bootstrap/assets/images/ecoprint2.png" class="logo-icon" alt="logo icon">
       <h5 class="logo-text">E c o P r i n t </h5>
     </a>
   </div>
   <ul class="sidebar-menu do-nicescrol">
        <?php if($role === "admin"): ?>
        <li class="sidebar-header">ADMIN</li>
        <li
            <?= current_url() == base_url('index.php/admin') ? ' class="active"' : ''?>>
            <a href="<?=base_url()?>admin">
                <i class="zmdi zmdi-view-dashboard"></i> <span>Dashboard</span>
            </a>
        </li>

        <li
            <?= current_url() == base_url('index.php/admin/users') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>admin/users">
                <i class="zmdi zmdi-accounts"></i> <span>List of Users</span>
            </a>
        </li>

        <li
            <?= current_url() == base_url('index.php/admin/recyclable-bottles') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>admin/recyclable-bottles">
                <i class="zmdi zmdi-battery"></i> <span>Recyclable Bottles</span>
            </a>
        </li>

        <li
            <?= current_url() == base_url('index.php/admin/reverse-vending-machines') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>admin/reverse-vending-machines">
                <i class="fa fa-recycle"></i> <span>Reverse Vending Machines</span>
            </a>
        </li>
        
        <li
            <?= str_starts_with(current_url(), base_url('index.php/admin/printing-service')) ? 'class="active"' : '' ?>>
            <a href="<?=base_url()?>admin/printing-service">
                <i class="fa fa-print"></i> <span>Printing Service</span>
            </a>
        </li>

        <li
            <?= current_url() == base_url('index.php/admin/admin-and-office') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>admin/admin-and-office">
                <i class="fa fa-desktop"></i> <span>Admin and Offices</span>
            </a>
        </li>
        
        <?php elseif($role === "print"): ?>
        <li class="sidebar-header">PARTICIPATING OFFICE</li>
        <li
            <?= str_starts_with(current_url(), base_url('index.php/print/print-logs')) ? 'class="active"' : '' ?>>
            <a href="<?=base_url()?>print/print-logs">
                <i class="fa fa-print"></i> <span>Printing Logs</span>
            </a>
        </li>

        <?php else: ?>
        <?php endif; ?>
        
        <li class="sidebar-header">ECOUSER</li>
        <li <?=current_url() == base_url('index.php/dashboard') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>dashboard">
                <i class="zmdi zmdi-view-dashboard"></i> <span>My Eco</span>
            </a>
        </li>

        <li <?=current_url() == base_url('index.php/dashboard/recycling-logs') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>dashboard/recycling-logs">
                <i class="fa fa-recycle"></i> <span>My Recycling Logs</span>
            </a>
        </li>
        
        <li <?=current_url() == base_url('index.php/dashboard/files') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>dashboard/files">
                <i class="fa fa-print"></i> <span>My Files</span>
            </a>
        </li>

        <li <?=current_url() == base_url('index.php/dashboard/credit-transfers') ? 'class="active"' : ''?>>
            <a href="<?=base_url()?>dashboard/credit-transfers">
                <i class="fa fa-usd"></i> <span>Credit Transfer</span>
            </a>
        </li>
        <li>
            <a data-toggle="modal" data-target="#logoutModal">
                <i class="fa fa-sign-out"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
   
   </div>

<!-- Modals -->
 <style {csp-style-nonce}>
    .toast-local {
      z-index: 999999999; 
      right: 0; 
      top: 0;
      width: 300px
    }
    .down-icon{
        font-size: 1.5rem;
        color: #ff0707ff;
    }
    .error-icon{
        font-size: 1.5rem;
        color: #ff9f00ff;
    }
    #icon {
      font-size: 5rem;
      color: #0087D7;
    }
    #toast-icon {
      font-size: 1.5rem;
    }
    .text-md {
      font-size: 1.5rem;
    }
    .blue-button {
      background-color: #0087D7;
      color: white;
    }
    .toast-body {
      color: black;
    }
  </style>
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="fa fa-sign-out" id="icon"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to logout?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn blue-button" data-dismiss="modal">Cancel</button>
                    <a href="<?= base_url('logout') ?>" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
 <!-- Toast -->
  <div class="position-fixed bottom-0 right-0 p-3 toast-local">
    <div class="toast hide machine-toast" aria-live="assertive" aria-atomic="true" id="machine-toast" role="alert" data-autohide="false" data-delay="5000">
      <div class="toast-header">
        <i class="" id="toast-icon"></i>
        <strong class="mr-auto">EcoPrint</strong>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="toast-body" id="toast-message">
        ...
      </div>
    </div>
  </div>

<!--Start topbar header-->
<header class="topbar-nav">
 <nav class="navbar navbar-expand fixed-top">
  <ul class="navbar-nav mr-auto align-items-center">
    <li class="nav-item">
      <a class="nav-link toggle-menu" href="javascript:void();">
       <i class="icon-menu menu-icon"></i>
     </a>
    </li>
  </ul>
</nav>
</header>
<!--End topbar header-->

<div class="clearfix"></div>
	
  <div class="content-wrapper">
    <div class="container-fluid">
            <?= $this->renderSection('content') ?>
            

		  <div class="overlay toggle-menu"></div>
		
    </div>
    <!-- End container-fluid-->
    
    </div><!--End content-wrapper-->
   <!--Start Back To Top Button-->
    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
    <!--End Back To Top Button-->
	
	<!--Start footer-->
	<footer class="footer">
      <div class="container">
        <div class="text-center">
          Copyright © 2025 EcoPrint
        </div>
      </div>
    </footer>
	<!--End footer-->
	
  <!--start color switcher-->
   <div class="right-sidebar">
    <div class="switcher-icon">
      <i class="zmdi zmdi-settings zmdi-hc-spin"></i>
    </div>
    <div class="right-sidebar-content">

      <p class="mb-0">Gaussion Texture</p>
      <hr>
      
      <ul class="switcher">
        <li id="theme1"></li>
        <li id="theme2"></li>
        <li id="theme3"></li>
        <li id="theme4"></li>
        <li id="theme5"></li>
        <li id="theme6"></li>
      </ul>

      <p class="mb-0">Gradient Background</p>
      <hr>
      
      <ul class="switcher">
        <li id="theme7"></li>
        <li id="theme8"></li>
        <li id="theme9"></li>
        <li id="theme10"></li>
        <li id="theme11"></li>
        <li id="theme12"></li>
		    <li id="theme13"></li>
        <li id="theme14"></li>
        <li id="theme15"></li>
      </ul>
      
     </div>
   </div>
  <!--end color switcher-->
   
  </div><!--End wrapper-->

  <!-- Bootstrap core JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
	
 <!-- simplebar js -->
  <script src="<?=base_url()?>bootstrap/assets/plugins/simplebar/js/simplebar.js"></script>
  <!-- sidebar-menu js -->
  <script src="<?=base_url()?>bootstrap/assets/js/sidebar-menu.js"></script>
  <!-- Custom scripts -->
  <script src="<?=base_url()?>bootstrap/assets/js/app-script.js"></script>


  <script {csp-script-nonce}>
    function renderMachinesStatus(machines) {
        if (machines && machines.length > 0) {
            machines.forEach(machine => {
                const status_class = machine.status.toLowerCase();
                if(status_class === 'down'){
                    document.getElementById('toast-icon').className = "fa fa-exclamation-circle down-icon";
                    document.getElementById('toast-message').innerHTML = `${machine.machine_name} is currently down.`;
                    $('#machine-toast').toast('show');
                } else if(status_class === 'error'){
                    document.getElementById('toast-icon').className = "fa fa-exclamation-triangle error-icon";
                    document.getElementById('toast-message').innerHTML = `${machine.machine_name} is experiencing an error.`;
                    $('#machine-toast').toast('show');
                } else if(status_class === 'full'){
                    document.getElementById('toast-icon').className = "fa fa-exclamation-triangle error-icon";
                    document.getElementById('toast-message').innerHTML = `${machine.machine_name} is currently full.`;
                    $('#machine-toast').toast('show');
                }
            });
        }

    }

    // --- 2. Define the polling function ---
    function pollMachineStatus() {
        const url = '<?= base_url('api/machine/get-status') ?>';
        console.log('Fetching machine data from after 5 seconds:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // The data is the array of machines returned by the controller
                renderMachinesStatus(data);
                console.log('Machine data updated successfully.');
            })
            .catch(error => {
                console.error('Error fetching machine data:', error);
                // Optionally display an error message to the user
            });
    }

    <?php if($role === "admin" && current_url() != base_url('index.php/admin/reverse-vending-machines')): ?>
      // --- 3. Start Polling ---
      
      // Run immediately on page load to display initial data
      pollMachineStatus(); 
      
      // Set up polling (e.g., every 15 seconds = 15000 milliseconds)
      const pollingInterval = 5000; 
      setInterval(pollMachineStatus, pollingInterval);
    <?php endif; ?>

</script>
</body>
</html>