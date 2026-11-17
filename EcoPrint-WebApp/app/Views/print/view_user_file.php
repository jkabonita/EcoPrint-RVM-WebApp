<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<style {csp-style-nonce}>
    /* Ensure the PDF viewer container takes full width */
    #pdf-viewer-container {
        width: 50%;
        height: 800px;
        overflow-y: scroll;
        border: 1px solid #ccc;
        background-color: #f5f5f5;
    }

    /* Style for each canvas to ensure responsiveness */
    #pdf-viewer-container canvas {
        width: 100% !important; /* Make canvas take full width of its container */
        height: auto !important; /* Maintain aspect ratio */
        display: block; /* Remove any inline spacing */
        margin: 0 auto; /* Center the canvas */
    }
</style>
<?php
    // PHP code to transform the array into a simple JSON object for JavaScript
    $pagePrices = [];
    foreach ($pdfResult['pages'] as $page) {
        // Page number (1-based) is the key, price is the value.
        $pagePrices[$page['page']] = (float)$page['price'];
    }
    $pagePricesJson = json_encode($pagePrices);
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="post" class="my-3 mx-5" id="printForm" action="<?= base_url('api/print/process-print/') . $jobId ?>">
                    <?= csrf_field() ?>
                    <?php
                    $success = session()->getFlashdata('success') ?? ($_GET['success'] ?? null);
                    $error = session()->getFlashdata('error') ?? ($_GET['error'] ?? null);
                    ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success p-2"><?= esc($success) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger p-2"><?= esc($error) ?></div>
                    <?php endif; ?>
                    <div class="form-group mb-4 p-3 border rounded">
                        <label class="font-weight-bold">Pages</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="pagesAll" name="pagesOption" class="custom-control-input" value="all" checked>
                            <label class="custom-control-label" for="pagesAll">All</label>
                        </div>
                        <div class="custom-control custom-radio mt-2">
                            <input type="radio" id="pagesCustom" name="pagesOption" class="custom-control-input" value="custom">
                            <label class="custom-control-label" for="pagesCustom">Specific pages/ranges</label>
                        </div>
                        <div class="mt-2 ml-4">
                            <input type="text" class="form-control" id="pageRangeInput" name="pageRange" placeholder="e.g. 1-5, 8, 11-13" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="numPagesInput">Number of Pages</label>
                        <input type="number" class="form-control" id="numPagesInput" name="numPages" value="<?= esc($pages_printed) ?>" readonly>
                        <label for="copiesInput">Copies</label>
                        <input type="number" class="form-control" id="copiesInput" name="numCopies" min="1">
                        <label for="amount">Total Price:</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="<?= esc($total_amount) ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-light btn-block">Print</button>
                </form>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Page Number</th>
                            <th>Page Size</th>
                            <th>Colored / With Image</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pdfResult['pages'] as $page): ?>
                            <tr>
                                <td><?= esc($page['page']) ?></td>
                                <td><?= esc($page['size']) ?></td>
                                <td><?= $page['isColor'] ? 'Yes' : 'No' ?></td>
                                <td><?= number_format($page['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <h4>Viewing: <?= esc($file['original_filename']) ?></h4>
        <div class="mb-3">
            <a href="<?= site_url('print/user-files') ?>" class="btn btn-secondary">Back</a>
        </div>

        <?php
            $ext = pathinfo($fileUrl, PATHINFO_EXTENSION);
        ?>

        <?php if (strtolower($ext) === 'pdf'): ?>
            <div id="pdf-viewer-container">
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
            <script {csp-script-nonce}>
                // Set the workerSrc for PDF.js
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

                const url = '<?= $fileUrl ?>';
                const pdfViewerContainer = document.getElementById('pdf-viewer-container');
                const scale = 1.5; // Adjust this for default zoom level

                // --- NEW PRICE DATA & ELEMENT REFERENCES ---
                // This JSON object maps page number (1-based) to its base price.
                const PAGE_PRICES = JSON.parse('<?= $pagePricesJson ?>'); 
                
                const pagesAll = document.getElementById('pagesAll');
                const pagesCustom = document.getElementById('pagesCustom');
                const pageRangeInput = document.getElementById('pageRangeInput'); 
                const numPagesInput = document.getElementById('numPagesInput');
                const copiesInput = document.getElementById('copiesInput');
                const amountInput = document.getElementById('amount');
                let pdfDoc = null; // Will be set after the PDF loads
                
                // --- KEY ADDITION: Array to hold promises for all page renderings ---
                const pagePromises = [];
                const canvasElements = []; // Array to store canvas elements in page order

                /**
                 * Parses a page range string (e.g., "1-5, 8, 11-13") into an array of unique page numbers.
                 * @param {string} rangeString - The page range string.
                 * @param {number} totalPages - The total number of pages in the document.
                 * @returns {number[]} - An array of valid, unique page numbers to print.
                 */
                function getPagesToPrint(rangeString, totalPages) {
                    if (!rangeString) return [];
                    
                    const pageNumbers = new Set();
                    const ranges = rangeString.split(',').map(s => s.trim()).filter(s => s.length > 0);
                    console.log("Parsing page ranges:", ranges);

                    for (const range of ranges) {
                        if (range.includes('-')) {
                            const [startStr, endStr] = range.split('-').map(s => s.trim());
                            const start = parseInt(startStr);
                            let end = parseInt(endStr);
                            
                            // Handle open-ended ranges like "10-"
                            if (isNaN(end)) {
                                end = totalPages;
                            }

                            if (!isNaN(start) && !isNaN(end) && start > 0 && end > 0 && start <= end) {
                                for (let i = start; i <= end; i++) {
                                    if (i <= totalPages) {
                                        pageNumbers.add(i);
                                    }
                                }
                            }
                        } else {
                            const pageNum = parseInt(range);
                            if (!isNaN(pageNum) && pageNum > 0 && pageNum <= totalPages) {
                                pageNumbers.add(pageNum);
                            }
                        }
                    }
                    
                    // Convert Set to Array, sort, and return
                    console.log("Pages to print after parsing details:", Array.from(pageNumbers).sort((a, b) => a - b));
                    return Array.from(pageNumbers).sort((a, b) => a - b);
                }

                /**
                 * Updates the displayed number of pages, total price, and PDF viewer visibility.
                 */
                function updatePrintInfo() {
                    if (!pdfDoc) return; // Ensure PDF is loaded
                    
                    const numPages = pdfDoc.numPages;
                    let pagesToPrintArray = [];

                    if (pagesAll.checked) {
                        // 'All' is selected, pages to print is all pages
                        pagesToPrintArray = Array.from({ length: numPages }, (_, i) => i + 1);
                    } else if (pagesCustom.checked) {
                        // 'Custom' is selected, parse the range input
                        pagesToPrintArray = getPagesToPrint(pageRangeInput.value, numPages);
                    }

                    const printedPagesCount = pagesToPrintArray.length;
                    numPagesInput.value = printedPagesCount;

                    // --- Update Total Price (using individual page prices) ---
                    const numCopies = parseInt(copiesInput.value) || 1;
                    copiesInput.value = numCopies; // Ensure the copies field shows at least 1
                    
                    let subtotalPrice = 0;
                    
                    // Sum the prices for all selected pages
                    for (const pageNum of pagesToPrintArray) {
                        // Use the price from the PAGE_PRICES map. If somehow missing, default to 0.
                        subtotalPrice += PAGE_PRICES[pageNum] || 0; 
                    }

                    const totalPrice = subtotalPrice * numCopies;
                    amountInput.value = totalPrice.toFixed(2); // Display price with two decimal places

                    // --- Update PDF Viewer Visibility ---
                    // Iterate through the pre-sorted array of canvas elements
                    canvasElements.forEach((canvas, index) => {
                        // PDF page numbers are 1-based, and our canvasElements array is indexed 0 to N-1
                        const pageNumber = index + 1; 
                        
                        // Check if the page number is in the array of pages to print
                        const isVisible = pagesToPrintArray.includes(pageNumber);

                        // Hide or show the canvas
                        canvas.style.display = isVisible ? 'block' : 'none';
                    });
                }

                function togglePageRangeInput() {
                    pageRangeInput.disabled = pagesAll.checked;
                    if (pagesAll.checked) {
                        // Clear input when 'All' is selected, but don't call updatePrintInfo() here
                        // it will be called by the 'change' event listener below.
                        pageRangeInput.value = ''; 
                    }
                }


                pdfjsLib.getDocument(url).promise.then(function(loadedPdfDoc) {
                    pdfDoc = loadedPdfDoc;
                    const numPages = pdfDoc.numPages;
                    document.getElementById('numPagesInput').value = numPages; 

                    // Initial state setup
                    togglePageRangeInput();

                    // Loop through each page and **initiate rendering and store the promise/canvas**
                    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                        const pagePromise = pdfDoc.getPage(pageNum).then(function(page) {
                            const viewport = page.getViewport({ scale: scale });

                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            
                            // Assign a data attribute for easy identification
                            canvas.setAttribute('data-page-num', pageNum);
                            canvas.style.marginBottom = '10px';

                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            // Set CSS dimensions to control display size
                            canvas.style.width = '100%'; 
                            canvas.style.height = 'auto'; 

                            const renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };

                            // The render promise must complete before we consider the page 'ready'
                            return page.render(renderContext).promise.then(() => {
                                // Return the canvas after successful rendering
                                return canvas; 
                            });
                        });
                        
                        // Store the promise for this page's canvas
                        pagePromises.push(pagePromise);
                    }
                    
                    // --- KEY CORRECTION: Wait for all pages to render, then append in order ---
                    Promise.all(pagePromises).then(canvases => {
                        // The 'canvases' array will be in the same order as 'pagePromises' (i.e., page 1, 2, 3...)
                        // Clear the container before appending
                        pdfViewerContainer.innerHTML = ''; 
                        
                        canvases.forEach(canvas => {
                            pdfViewerContainer.appendChild(canvas);
                            canvasElements.push(canvas); // Populate the global array
                        });

                        // Now that all elements are in the DOM, call updatePrintInfo for initial calculation and visibility
                        updatePrintInfo(); 

                        // Add event listeners for interactivity only AFTER the PDF is fully loaded and DOM is structured
                        // Radio buttons
                        pagesAll.addEventListener('change', () => {
                            togglePageRangeInput();
                            updatePrintInfo();
                        });
                        pagesCustom.addEventListener('change', () => {
                            togglePageRangeInput();
                            updatePrintInfo();
                        });

                        // Custom page range input (debounce to prevent lag)
                        pageRangeInput.addEventListener('input', () => {
                            if (pagesCustom.checked) {
                                clearTimeout(pageRangeInput.delayTimer);
                                pageRangeInput.delayTimer = setTimeout(updatePrintInfo, 500);
                            }
                        });

                        // Copies input
                        copiesInput.addEventListener('input', updatePrintInfo);
                        
                    }).catch(function(error) {
                        console.error("Error rendering PDF pages: ", error);
                        alert("Could not render all PDF pages. Error: " + error.message);
                    });
                    // --- END KEY CORRECTION ---

                }).catch(function(error) {
                    console.error("Error loading PDF: ", error);
                    alert("Could not load PDF. Please try again later. Error: " + error.message);
                });
            </script>

            <<script {csp-script-nonce}>
                document.getElementById('printForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const form = this;
                    const formData = new FormData(form);

                    // Show loading overlay
                    showLoading("Sending to printer...");

                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => { throw new Error(data.message || 'Server error.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success' && data.uri) {
                            // Trigger the print app
                            window.location.href = data.uri;

                            // Start polling the print job status
                            startPolling(<?= $jobId ?>);
                        } else {
                            throw new Error(data.message || 'Invalid server response.');
                        }
                    })
                    .catch(err => {
                        hideLoading();
                        showError("Failed to start print: " + err.message);
                    });
                });

                function showLoading(message) {
                    const overlay = document.createElement('div');
                    overlay.id = 'loading-overlay';
                    overlay.style.position = 'fixed';
                    overlay.style.top = 0;
                    overlay.style.left = 0;
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.background = 'rgba(255, 255, 255, 0.95)';
                    overlay.style.zIndex = 99999;
                    overlay.innerHTML = `
                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                            <div class="spinner-border text-primary" style="width:4rem; height:4rem;"></div>
                            <p class="mt-3 h5 text-dark" id="loading-message">${message}</p>
                        </div>
                    `;
                    document.body.appendChild(overlay);
                }

                function updateLoadingMessage(message) {
                    const msgEl = document.getElementById('loading-message');
                    if (msgEl) msgEl.innerText = message;
                }

                function hideLoading() {
                    const overlay = document.getElementById('loading-overlay');
                    if (overlay) overlay.remove();
                }

                function showError(msg) {
                    window.location.href = "<?= current_url() ?>?error=" + encodeURIComponent(msg);
                }

                function showSuccess(msg) {
                    window.location.href = "<?= current_url() ?>?success=" + encodeURIComponent(msg);
                }

                function startPolling(jobId) {
                    let elapsed = 0;
                    const intervalTime = 2000; // 2 seconds
                    const timeoutLimit = 120000; // 2 minutes max

                    const interval = setInterval(() => {
                        elapsed += intervalTime;

                        fetch("<?= base_url('api/print/job-status/') ?>" + jobId)
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    const jobStatus = data.job_status;

                                    if (jobStatus === 'Printing') {
                                        updateLoadingMessage("Printing Document...");
                                    } else if (jobStatus === 'Completed') {
                                        clearInterval(interval);
                                        hideLoading();
                                        showSuccess("Printing completed successfully!");
                                    } else if (jobStatus === 'Failed' || jobStatus === 'Error') {
                                        clearInterval(interval);
                                        hideLoading();
                                        showError("Printing failed.");
                                    }
                                } else {
                                    clearInterval(interval);
                                    hideLoading();
                                    showError(data.message || "Unknown error while checking status.");
                                }
                            })
                            .catch(() => {
                                clearInterval(interval);
                                hideLoading();
                                showError("Error connecting to server while checking print status.");
                            });

                        // Stop polling if timeout reached
                        if (elapsed >= timeoutLimit) {
                            clearInterval(interval);
                            hideLoading();
                            showError("Print job timed out. Please try again.");
                        }
                    }, intervalTime);
                }
                </script>

        <?php else: ?>
            <p>Preview not supported. <a href="<?= $filePath ?>" target="_blank">Download or open in new tab</a></p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>