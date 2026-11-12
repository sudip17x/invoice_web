<?php
/**
 * Shared by the PDF download (Dompdf, $mode = 'pdf') and the browser print page ($mode = 'print').
 * Table layout + inline CSS only: Dompdf supports CSS 2.1, not flex/grid.
 *
 * @var array  $invoice  invoice row + joined customer_* columns
 * @var array  $items    invoice_items rows
 * @var array  $settings company settings row
 * @var ?string $logoSrc data URI (pdf) or URL (print), or null
 * @var string $mode     'pdf' | 'print'
 */
helper('app');
$isPrint  = $mode === 'print';
$currency = currency_symbol();
$qty      = static fn ($q): string => rtrim(rtrim(number_format((float) $q, 2, '.', ''), '0'), '.');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($invoice['invoice_no']) ?></title>
    <style>
        @page { size: A4 portrait; margin: 16mm 14mm 18mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #222; margin: 0; }
        h1 { font-size: 22px; margin: 0 0 4px 0; letter-spacing: 1px; color: #1f2a44; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; }
        .muted { color: #666; }
        .label { font-size: 8px; text-transform: uppercase; color: #666; letter-spacing: .5px; margin-bottom: 2px; }
        .pre { white-space: pre-line; }
        .items { margin-top: 14px; }
        .items thead { display: table-header-group; }               /* repeat header on every page */
        .items th { background: #1f2a44; color: #fff; padding: 6px; text-align: left; font-size: 9px; }
        .items td { padding: 6px; border-bottom: 1px solid #ddd; vertical-align: top; }
        .items tr { page-break-inside: avoid; }                      /* never split a row across pages */
        .r { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 12px; page-break-inside: avoid; }
        .totals td { padding: 4px 6px; }
        .totals .grand td { border-top: 2px solid #1f2a44; font-weight: bold; font-size: 12px; }
        .box { page-break-inside: avoid; margin-top: 16px; }
        .footer { margin-top: 18px; text-align: center; color: #666; font-size: 9px; }
        .badge { display: inline-block; padding: 2px 8px; border: 1px solid #1f2a44; font-size: 9px; text-transform: uppercase; }
        .logo { max-height: 60px; max-width: 180px; }

        .toolbar { padding: 12px; background: #f1f3f5; text-align: center; }
        .toolbar button, .toolbar a { font: 14px Arial, sans-serif; padding: 6px 14px; margin: 0 4px; cursor: pointer; }
        <?php if ($isPrint): ?>
        body { max-width: 794px; margin: 0 auto; padding: 24px; }
        @media print {
            .no-print, .toolbar { display: none !important; }
            body { max-width: none; padding: 0; }
        }
        <?php endif; ?>
    </style>
</head>
<body>
<?php if ($isPrint): ?>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Print</button>
        <a href="<?= site_url('invoices/view/' . (int) $invoice['id']) ?>">Back to invoice</a>
    </div>
<?php endif; ?>

<table class="meta">
    <tr>
        <td style="width:55%">
            <?php if ($logoSrc): ?><img class="logo" src="<?= esc($logoSrc, 'attr') ?>" alt=""><br><?php endif; ?>
            <strong style="font-size:13px"><?= esc($settings['company_name']) ?></strong>
            <div class="pre"><?= esc($settings['address']) ?></div>
            <?php if ($settings['phone']): ?><div>Tel: <?= esc($settings['phone']) ?></div><?php endif; ?>
            <?php if ($settings['email']): ?><div><?= esc($settings['email']) ?></div><?php endif; ?>
            <?php if ($settings['tax_id']): ?><div>GSTIN / Tax ID: <?= esc($settings['tax_id']) ?></div><?php endif; ?>
        </td>
        <td class="r">
            <h1>INVOICE</h1>
            <div><strong><?= esc($invoice['invoice_no']) ?></strong></div>
            <div class="muted">Issue date: <?= esc($invoice['issue_date']) ?></div>
            <div class="muted">Due date: <?= esc($invoice['due_date']) ?></div>
            <div style="margin-top:6px"><span class="badge"><?= esc(strtoupper($invoice['status'])) ?></span></div>
        </td>
    </tr>
</table>

<table class="meta" style="margin-top:18px">
    <tr>
        <td style="width:55%">
            <div class="label">Bill to</div>
            <strong><?= esc($invoice['customer_name']) ?></strong>
            <div class="pre"><?= esc($invoice['customer_address']) ?></div>
            <?php if ($invoice['customer_email']): ?><div><?= esc($invoice['customer_email']) ?></div><?php endif; ?>
            <?php if ($invoice['customer_phone']): ?><div>Tel: <?= esc($invoice['customer_phone']) ?></div><?php endif; ?>
            <?php if ($invoice['customer_tax_id']): ?><div>GSTIN / Tax ID: <?= esc($invoice['customer_tax_id']) ?></div><?php endif; ?>
        </td>
        <td></td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th>Description</th>
            <th class="r" style="width:9%">Qty</th>
            <th class="r" style="width:14%">Unit price</th>
            <th class="r" style="width:8%">Tax %</th>
            <th class="r" style="width:15%">Amount</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $n => $it): ?>
        <tr>
            <td><?= $n + 1 ?></td>
            <td><?= esc($it['description']) ?></td>
            <td class="r"><?= esc($qty($it['qty'])) ?></td>
            <td class="r"><?= money($it['unit_price']) ?></td>
            <td class="r"><?= money($it['tax_percent']) ?></td>
            <td class="r"><?= money($it['line_total']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal</td><td class="r"><?= money($invoice['subtotal']) ?></td></tr>
    <tr><td>Discount</td><td class="r">- <?= money($invoice['discount']) ?></td></tr>
    <tr><td>Tax</td><td class="r"><?= money($invoice['tax_total']) ?></td></tr>
    <tr class="grand"><td>Grand total</td><td class="r"><?= esc($currency) ?> <?= money($invoice['grand_total']) ?></td></tr>
</table>

<?php if ($invoice['notes']): ?>
    <div class="box">
        <div class="label">Notes</div>
        <div class="pre"><?= esc($invoice['notes']) ?></div>
    </div>
<?php endif; ?>

<?php if ($settings['bank_details']): ?>
    <div class="box">
        <div class="label">Bank details</div>
        <div class="pre"><?= esc($settings['bank_details']) ?></div>
    </div>
<?php endif; ?>

<?php if ($settings['footer_note']): ?>
    <div class="footer pre"><?= esc($settings['footer_note']) ?></div>
<?php endif; ?>
</body>
</html>
