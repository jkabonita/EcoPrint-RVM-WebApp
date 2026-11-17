<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <title>EcoPrint - Log In</title>
  
  <link rel="icon" href="<?= base_url('bootstrap/assets/images/eco2.ico') ?>" type="image/x-icon">

  <link href="<?= base_url('bootstrap/assets/css/bootstrap.min.css') ?>" rel="stylesheet"/>
  <link href="<?= base_url('bootstrap/assets/css/animate.css') ?>" rel="stylesheet"/>
  <link href="<?= base_url('bootstrap/assets/css/icons.css') ?>" rel="stylesheet"/>
  <link href="<?= base_url('bootstrap/assets/css/ic.css') ?>" rel="stylesheet"/>
  <link href="<?= base_url('bootstrap/assets/css/app-style.css') ?>" rel="stylesheet"/>

  <style {csp-script-nonce}>
    #googleLoginBtn {
      pointer-events: none;
      opacity: 0.6;
    }

    .small-label {
      font-size: 0.875em;
    }
  </style>
</head>

<body class="bg-theme bg-theme1">

<div id="pageloader-overlay" class="visible incoming">
  <div class="loader-wrapper-outer">
    <div class="loader-wrapper-inner">
      <div class="loader"></div>
    </div>
  </div>
</div>

<div id="wrapper" class="mt-5">
  <div class="card card-authentication1 mx-auto">
    <div class="card-body">
      <div class="card-content p-2">
        <div class="text-center mb-3">
          <img src="<?= base_url('bootstrap/assets/images/eco2.png') ?>" class="img-fluid h-auto w-50" alt="EcoPrint Logo">
        </div>
        <div class="card-title text-uppercase text-center py-2">Register your account by signing in with your CSPC Email</div>
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
          <?php endif; ?>
          <div class="container">
            <div class="row">
              <?php if (config('Recaptcha')->enabled): ?>
                <div class="g-recaptcha mx-auto my-2" 
                    data-sitekey="<?= esc(config('Recaptcha')->siteKey) ?>"
                    data-callback="enableLoginButton" 
                    data-expired-callback="disableLoginButton"></div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
              <?php endif; ?>
            </div>
          </div>

        <hr class="text-white">
        <div class="form-check text-center mb-3">
          <input type="checkbox" class="form-check-input" id="agreeCheck">
          <label class="form-check-label text-white text-capitalize" for="agreeCheck">
            I agree to the 
            <a href="<?= base_url('privacy-policy') ?>" target="_blank" class="text-info">Privacy Policy</a>
            and
          </label>
          <label class="form-check-label text-white text-capitalize" for="agreeCheck">
            <a href="<?= base_url('terms-of-service') ?>" target="_blank" class="text-info">Terms of Service</a>.
          </label>
        </div>

        <div class="text-center mb-3">
          <a id="googleLoginBtn" href="<?= base_url('/auth/google-login') ?>" class="btn btn-success btn-block">
            <i class="zmdi zmdi-google zmdi-hc-lg mr-2"></i> Sign in with CSPC Email
          </a>
        </div>
      </div>
    </div>

    
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="<?= base_url('bootstrap/assets/js/sidebar-menu.js') ?>"></script>

<script {csp-script-nonce}>
  let recaptchaVerified = false;
  const btn = document.getElementById('googleLoginBtn');
  const agreeCheck = document.getElementById('agreeCheck');

  function updateButtonState() {
    if (recaptchaVerified && agreeCheck.checked) {
      btn.classList.remove('disabled');
      btn.style.pointerEvents = 'auto';
      btn.style.opacity = '1';
    } else {
      btn.classList.add('disabled');
      btn.style.pointerEvents = 'none';
      btn.style.opacity = '0.6';
    }
  }

  function enableLoginButton() {
    recaptchaVerified = true;
    updateButtonState();
  }

  function disableLoginButton() {
    recaptchaVerified = false;
    updateButtonState();
  }

  // When checkbox changes, recheck state
  agreeCheck.addEventListener('change', updateButtonState);

  // Disable button on load
  document.addEventListener('DOMContentLoaded', disableLoginButton);
</script>

</body>
</html>
