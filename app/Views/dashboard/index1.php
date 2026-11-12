<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total invoices</div>
            <div class="value"><?= (int) $stats['total'] ?></div>
            <div class="text-muted small"><?= (int) $stats['this_month'] ?> this month</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card paid shadow-sm"><div class="card-body">
            <div class="text-muted small">Paid amount</div>
            <div class="value"><?= esc(currency_symbol()) ?> <?= money($stats['paid_amount']) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card unpaid shadow-sm"><div class="card-body">
            <div class="text-muted small">Unpaid amount</div>
            <div class="value"><?= esc(currency_symbol()) ?> <?= money($stats['unpaid_amount']) ?></div>
            <div class="text-muted small">Draft + sent</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card overdue shadow-sm"><div class="card-body">
            <div class="text-muted small">Overdue invoices</div>
            <div class="value"><?= (int) $stats['overdue_count'] ?></div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Latest invoices</strong>
        <a class="btn btn-sm btn-primary" href="<?= site_url('invoices/create') ?>">New invoice</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>No.</th><th>Customer</th><th>Issued</th><th>Due</th><th class="text-end">Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($latest as $inv): ?>
                <tr>
                    <td><a href="<?= site_url('invoices/view/' . $inv['id']) ?>"><?= esc($inv['invoice_no']) ?></a></td>
                    <td><?= esc($inv['customer_name']) ?></td>
                    <td><?= esc($inv['issue_date']) ?></td>
                    <td><?= esc($inv['due_date']) ?></td>
                    <td class="text-end"><?= money($inv['grand_total']) ?></td>
                    <td><?= status_badge($inv) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($latest === []): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No invoices yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
