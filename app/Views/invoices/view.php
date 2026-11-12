<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$status = $invoice['status'];
$id     = (int) $invoice['id'];

// Small POST form per status action (CSRF-protected, optional confirm dialog).
$statusButton = static function (string $to, string $label, string $class, string $confirm = '') use ($id): string {
    $onsubmit = $confirm !== '' ? ' onsubmit="return confirm(' . esc(json_encode($confirm), 'attr') . ');"' : '';

    return '<form class="d-inline" method="post" action="' . esc(site_url('invoices/status/' . $id), 'attr') . '"' . $onsubmit . '>'
        . csrf_field() . '<input type="hidden" name="status" value="' . esc($to, 'attr') . '">'
        . '<button class="btn ' . $class . '" type="submit">' . esc($label) . '</button></form> ';
};
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="<?= site_url('invoices/print/' . $id) ?>" target="_blank" rel="noopener">Print</a>
    <a class="btn btn-outline-secondary" href="<?= site_url('invoices/pdf/' . $id) ?>">Download PDF</a>

    <?php if ($status === 'draft'): ?>
        <a class="btn btn-outline-primary" href="<?= site_url('invoices/edit/' . $id) ?>">Edit</a>
        <?= $statusButton('sent', 'Mark as Sent', 'btn-primary') ?>
    <?php endif; ?>
    <?php if ($status === 'sent'): ?>
        <?= $statusButton('paid', 'Mark as Paid', 'btn-success') ?>
    <?php endif; ?>
    <?php if (in_array($status, ['draft', 'sent'], true)): ?>
        <?= $statusButton('cancelled', 'Cancel invoice', 'btn-outline-danger', 'Cancel this invoice? This cannot be undone.') ?>
    <?php endif; ?>
    <?php if (in_array($status, ['draft', 'cancelled'], true)): ?>
        <form class="d-inline" method="post" action="<?= site_url('invoices/delete/' . $id) ?>"
              onsubmit="return confirm('Delete this invoice permanently?');">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger" type="submit">Delete</button>
        </form>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Invoice</div>
                <div class="h5 mb-1"><?= esc($invoice['invoice_no']) ?> <?= status_badge($invoice) ?></div>
                <div>Issued: <?= esc($invoice['issue_date']) ?></div>
                <div>Due: <?= esc($invoice['due_date']) ?></div>
            </div>
            <div class="col-md-8">
                <div class="text-muted small">Bill to</div>
                <div class="fw-semibold"><?= esc($invoice['customer_name']) ?></div>
                <div style="white-space: pre-line"><?= esc($invoice['customer_address']) ?></div>
                <?php if ($invoice['customer_email']): ?><div><?= esc($invoice['customer_email']) ?></div><?php endif; ?>
                <?php if ($invoice['customer_tax_id']): ?><div>Tax ID: <?= esc($invoice['customer_tax_id']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>#</th><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit price</th><th class="text-end">Tax %</th><th class="text-end">Line total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $n => $it): ?>
                <tr>
                    <td><?= $n + 1 ?></td>
                    <td><?= esc($it['description']) ?></td>
                    <td class="text-end"><?= esc(rtrim(rtrim((string) $it['qty'], '0'), '.')) ?></td>
                    <td class="text-end"><?= money($it['unit_price']) ?></td>
                    <td class="text-end"><?= money($it['tax_percent']) ?></td>
                    <td class="text-end"><?= money($it['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <?php if ($invoice['notes']): ?>
            <div class="text-muted small">Notes</div>
            <div style="white-space: pre-line"><?= esc($invoice['notes']) ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm"><div class="card-body">
            <dl class="row mb-0">
                <dt class="col-6 fw-normal">Subtotal</dt><dd class="col-6 text-end"><?= money($invoice['subtotal']) ?></dd>
                <dt class="col-6 fw-normal">Discount</dt><dd class="col-6 text-end">- <?= money($invoice['discount']) ?></dd>
                <dt class="col-6 fw-normal">Tax</dt><dd class="col-6 text-end"><?= money($invoice['tax_total']) ?></dd>
                <dt class="col-6">Grand total</dt><dd class="col-6 text-end fw-bold"><?= esc(currency_symbol()) ?> <?= money($invoice['grand_total']) ?></dd>
            </dl>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
