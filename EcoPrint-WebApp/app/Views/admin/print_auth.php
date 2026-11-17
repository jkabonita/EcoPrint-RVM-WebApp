<style {csp-style-nonce}> 
        .auth-container {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            text-align: center;
            position: relative;
        }

        .auth-container h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .auth-container p {
            font-size: 1rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .spinner {
            display: none; /* initially hidden */
            margin: 2rem auto;
            width: 50px;
            height: 50px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        input#uidInput {
            opacity: 0;
            position: absolute;
            left: 0;
            top:0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-50%) translateY(10px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
    </style>
    <div class="row">
        <div class="auth-container mx-auto my-5">
            <div class="alert alert-info p-2">Note: Please connect the RFID reader first then run the EcoPrint-Reader application</div>
            <div class="spinner"></div>
            <p>Please tap your RFID tag to log in...</p>
            

            <form id="uidForm" method="post" action="<?=base_url('admin/print-auth')?>">
                <?= csrf_field() ?>
                    <?php $success = session()->getFlashdata('success'); ?>
                    <?php if ($success): ?>
                        <div class="alert alert-info p-2"><?= esc($success) ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger p-2"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                <input type="text" name="uid" id="uidInput" tabindex="1" autofocus autocomplete="off">
            </form>
    </div>
    </div>

<script {csp-script-nonce}>
    const input = document.getElementById('uidInput');
    const spinner = document.querySelector('.spinner');
    const form = document.getElementById('uidForm');

    input.addEventListener('input', function () {
        console.log('Current input:', this.value); // log every input change
        if (this.value.length >= 8) {
            console.log("Submitting UID: " + this.value);
            document.getElementById('uidForm').submit();
            spinner.style.display = 'block';
            form.submit();
        }
    });

    // Keep the input focused
    setInterval(() => {
        if (document.activeElement !== input) {
            console.log('Input not focused — refocusing');
            input.focus();
        }
    }, 300);
</script>
