<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<style {csp-style-nonce}>
  @media (max-width: 768px) {
    iframe {
        height: 50vh !important; /* full viewport height */
    }
  }
  .mobile-pdf {
    width: 100%
  }
</style>
<div class="container mt-4">
    <h4>Viewing: <?= esc($file['original_filename']) ?></h4>
    <div class="mb-3">
        <a href="<?= site_url('dashboard/files') ?>" class="btn btn-info">Back</a>
        <a href="<?= site_url('dashboard/files/delete-file/' . $file['file_id']) ?>" 
            id="delete-file-btn-<?= $file['file_id'] ?>" 
            class="btn btn-danger">Delete File</a>
    </div>

    <?php
        $filePath = base_url('public/uploads/documents/' . $file['filename']);
        $ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
    ?>

    <?php if (strtolower($ext) === 'pdf'): ?>
    <?php
        // Detect if it's a mobile device
        $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $_SERVER['HTTP_USER_AGENT']);
    ?>

    <?php if ($isMobile): ?>
        <!-- For mobile browsers -->
        <iframe 
            src="https://docs.google.com/gview?url=<?= urlencode($filePath) ?>&embedded=true"
            class = "mobile-pdf" 
            frameborder="0">
        </iframe>
        <p class="mt-2">
            If the PDF doesn’t load, <a href="<?= $filePath ?>" target="_blank">tap here to open or download it</a>.
        </p>
    <?php else: ?>
        <!-- For desktop browsers -->
        <iframe src="<?= $filePath ?>" width="100%" height="800px"></iframe>
    <?php endif; ?>
<?php else: ?>
    <p>Preview not supported. <a href="<?= $filePath ?>" target="_blank">Download or open in new tab</a></p>
<?php endif; ?>
</div>


<script {csp-script-nonce}>
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById("delete-file-btn-<?= $file['file_id'] ?>");

    deleteBtn.addEventListener("click", function (e) {
        if (!confirm("Are you sure you want to delete this file?")) {
            e.preventDefault(); // stop navigation
        }
    });
});
</script>
<?= $this->endSection() ?>