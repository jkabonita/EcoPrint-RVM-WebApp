<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<style {csp-style-nonce}>
    .pdf-preview-container: {
      border: 1px solid #ccc; height: 600px; width: 100%;  
    }
    .pdf-viewer-iframe: {
      border: none;
    }
    #icon {
      font-size: 5rem;
      color: #0087D7;
    }
    .text-md {
      font-size: 1.5rem;
    }
  </style>
    <div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-dark">
                    <div class="modal-body text-dark d-flex flex-column align-items-center text-center">
                        <i class="zmdi zmdi-info-outline" id="icon"></i>
                        <p class="mt-3 mb-0 text-md" id="message">Are you sure you want to upload this file?</p>
                    </div>
                </div>
                <div class="modal-footer bg-blue">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" form="uploadFileForm">Upload</button>
                </div>
            </div>
        </div>
    </div>
<h2>Upload New File</h2>

<form action="<?= base_url('dashboard/files/upload-file') ?>" method="post" enctype="multipart/form-data" id="uploadFileForm">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="document" class="form-label">Select PDF Document</label>
        <input type="file" class="form-control" id="document" name="document" accept="application/pdf" required>
            <?php $error = session()->getFlashdata('error'); ?>
            <?php if ($error): ?>
                <div class="alert alert-danger my-3 p-3"><?= esc($error) ?></div>
            <?php endif; ?>
        <small class="form-text text-muted">Only PDF files up to 2MB are allowed.</small>
    </div>

    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#fileUploadModal">Upload File</button>
    <a href="<?= base_url('dashboard/files') ?>" class="btn btn-danger">Cancel</a>
</form>

<hr>

<h3>PDF Preview:</h3>
<div id="pdf-preview-container" class="pdf-preview-container">
    <iframe id="pdf-viewer-iframe" class="pdf-viewer-iframe" width="100%" height="600"></iframe>
</div>

<script {csp-script-nonce}>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('document');
    const pdfViewerIframe = document.getElementById('pdf-viewer-iframe');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.type === 'application/pdf') {
            const fileURL = URL.createObjectURL(file);
            // Construct the path to PDF.js viewer
            // Assuming pdfjs is in public/assets/pdfjs/
            const pdfjsViewerPath = '<?= base_url('public/assets/pdfjs/web/viewer.html') ?>';
            pdfViewerIframe.src = pdfjsViewerPath + '?file=' + encodeURIComponent(fileURL);
        } else {
            pdfViewerIframe.src = ''; // Clear iframe if not a PDF
            alert('Please select a PDF file.');
        }
    });
});
</script>
<?= $this->endSection() ?>