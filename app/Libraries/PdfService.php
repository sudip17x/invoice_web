<?php

declare(strict_types=1);

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    /**
     * Render the invoice template to A4 PDF bytes.
     *
     * @param array<string, mixed> $data view data for invoices/pdf_template
     */
    public function render(array $data): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // has the rupee sign
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('invoices/pdf_template', $data + ['mode' => 'pdf']));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // "Page X of Y" footer (A4 = 595 x 842 pt)
        $canvas = $dompdf->getCanvas();
        $font   = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text(500, 818, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.4, 0.4, 0.4]);

        return (string) $dompdf->output();
    }

    /** INV-2026-0001 -> INV-2026-0001.pdf (safe for Content-Disposition). */
    public function filename(string $invoiceNo): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '-', $invoiceNo) . '.pdf';
    }

    /**
     * Logo as a data URI so Dompdf never needs filesystem/remote access.
     */
    public function logoDataUri(?string $logoPath): ?string
    {
        if ($logoPath === null || $logoPath === '') {
            return null;
        }

        $full = WRITEPATH . 'uploads/' . basename($logoPath);

        if (! is_file($full)) {
            return null;
        }

        $mime = mime_content_type($full) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($full));
    }
}
