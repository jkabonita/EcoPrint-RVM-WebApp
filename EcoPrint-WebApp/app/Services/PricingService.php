<?php

namespace App\Services;

use Spatie\PdfToImage\Pdf;
use Smalot\PdfParser\Parser;
use App\Models\PrintingPricesModel;

class PricingService
{
    private array $pageSizePricing = [];

    public function __construct()
    {
        $printingPricesModel = new PrintingPricesModel();
        $prices = $printingPricesModel->findAll();

        foreach ($prices as $price) {
            $this->pageSizePricing[$price['variant_name']] = (float) $price['price'];
        }
    }

    public function analyzePdf(string $filePath, string $filename, int $office_id): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $details = $pdf->getDetails();

        $pageCount = (int) ($details['Pages'] ?? 0);
        $results = [];
        $total = 0;

        $pdfToImage = new Pdf($filePath);

        for ($page = 1; $page <= $pageCount; $page++) {
            $pdfToImage->selectPage($page);
            $imagePath = WRITEPATH . "uploads/{$office_id}_{$filename}_{$page}.jpg";
            $pdfToImage->save($imagePath);

            $isColor = $this->isColorPage($imagePath);
            $hasImage = $this->detectImageContent($imagePath);
            $pageSize = $this->detectPageSize($imagePath);

            $basePrice = $this->pageSizePricing[$pageSize] ?? $this->pageSizePricing['A4'];
            $extra = $isColor ? $this->pageSizePricing['Color/Image'] : 0;
            $pagePrice = $basePrice + $extra;
            $total += $pagePrice;

            $results[] = [
                'page' => $page,
                'size' => $pageSize,
                'isColor' => $isColor,
                'price' => $pagePrice
            ];
        }

        return [
            'pages' => $results,
            'total' => $total,
            'pageCount' => $pageCount
        ];
    }

    private function isColorPage(string $imagePath): bool
    {
        try {
            $imagick = new \Imagick($imagePath);
            $imagick->setImageColorspace(\Imagick::COLORSPACE_RGB);

            // Downscale for faster pixel analysis
            $imagick->scaleImage(100, 100, true);
            $pixels = $imagick->exportImagePixels(0, 0, $imagick->getImageWidth(), $imagick->getImageHeight(), "RGB", \Imagick::PIXEL_CHAR);

            $imagick->clear();
            $imagick->destroy();

            $colorThreshold = 15;  // difference threshold
            $sampleCount = count($pixels);
            $coloredPixels = 0;

            for ($i = 0; $i < $sampleCount; $i += 3) {
                $r = $pixels[$i];
                $g = $pixels[$i + 1];
                $b = $pixels[$i + 2];

                // If RGB values differ enough → it's a colored pixel
                if (abs($r - $g) > $colorThreshold || abs($g - $b) > $colorThreshold || abs($r - $b) > $colorThreshold) {
                    $coloredPixels++;
                }
            }

            // If more than 2% of pixels are colored, treat the page as color
            $ratio = $coloredPixels / ($sampleCount / 3);
            return $ratio > 0.02;
        } catch (\Throwable $e) {
            log_message('error', 'Color detection failed: ' . $e->getMessage());
            return false;
        }
    }


    private function detectImageContent(string $imagePath): bool
    {
        $imagick = new \Imagick($imagePath);
        $histogram = $imagick->getImageHistogram();
        return count($histogram) > 256; // simple heuristic
    }

    private function detectPageSize(string $imagePath): string
    {
        $imagick = new \Imagick($imagePath);
        $widthPx = $imagick->getImageWidth();
        $heightPx = $imagick->getImageHeight();

        // Get the actual image resolution (DPI). Use a fallback if not available.
        $dpi = $imagick->getImageResolution()['x'] ?? 96;

        // Convert pixels to millimeters (1 inch = 25.4 mm)
        $widthMm = ($widthPx / $dpi) * 25.4;
        $heightMm = ($heightPx / $dpi) * 25.4;

        // A small tolerance for comparison to account for minor variations and rounding errors
        $tolerance = 5; // 5 mm margin of error

        // Define the most common paper sizes in mm (width x height)
        $pageSizes = [
            'A0'     => [841, 1189],
            'A1'     => [594, 841],
            'A2'     => [420, 594],
            'A3'     => [297, 420],
            'A4'     => [210, 297],
            'A5'     => [148, 210],
            'A6'     => [105, 148],
            'LETTER' => [216, 279],
            'LEGAL'  => [216, 356],
            'TABLOID'=> [279, 432], // Also known as Ledger (when landscape)
            'FOLIO'  => [216, 330], // Common in some regions like parts of Asia and Latin America
        ];

        // Iterate through the defined sizes and check for a match
        foreach ($pageSizes as $name => $dimensions) {
            [$targetWidth, $targetHeight] = $dimensions;

            // Check for portrait orientation
            if (abs($widthMm - $targetWidth) < $tolerance && abs($heightMm - $targetHeight) < $tolerance) {
                return $name;
            }

            // Check for landscape orientation (width > height)
            if (abs($widthMm - $targetHeight) < $tolerance && abs($heightMm - $targetWidth) < $tolerance) {
                return $name . '_WIDE';
            }
        }

        // Return a default if no common page size is found
        return 'UNKNOWN';
    }
}
