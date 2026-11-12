
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
// Default dashboard values if the controller does not pass data.
$stats = $stats ?? [
    'total'          => 0,
    'this_month'     => 0,
    'paid_amount'    => 0,
    'unpaid_amount'  => 0,
    'overdue_count'  => 0,
];

$latest = $latest ?? [];
?>

<div class="row g-3 mb-4">

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total invoices</div>
                <div class="value"><?= (int) $stats['total'] ?></div>
                <div class="text-muted small">
                    <?= (int) $stats['this_month'] ?> this month
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card paid shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Paid amount</div>
                <div class="value">
                    ₹ <?= number_format((float) $stats['paid_amount'], 2) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card unpaid shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Unpaid amount</div>
                <div class="value">
                    ₹ <?= number_format((float) $stats['unpaid_amount'], 2) ?>
                </div>
                <div class="text-muted small">Draft + sent</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card overdue shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Overdue invoices</div>
                <div class="value">
                    <?= (int) $stats['overdue_count'] ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Latest invoices</strong>
        <a class="btn btn-sm btn-primary"
           href="<?= site_url('invoices/create') ?>">
            New invoice
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Customer</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($latest as $inv): ?>
                    <tr>
                        <td>
                            <a href="<?= site_url('invoices/view/' . $inv['id']) ?>">
                                <?= esc($inv['invoice_no']) ?>
                            </a>
                        </td>

                        <td><?= esc($inv['customer_name']) ?></td>
                        <td><?= esc($inv['issue_date']) ?></td>
                        <td><?= esc($inv['due_date']) ?></td>

                        <td class="text-end">
                            ₹ <?= number_format((float) $inv['grand_total'], 2) ?>
                        </td>

                        <td>
                              <?php
                                     $status = strtolower($inv['status'] ?? 'draft');

                                     $badgeClass = match ($status) {
                                     'paid'      => 'bg-success',
                                     'sent'      => 'bg-primary',
                                     'overdue'   => 'bg-danger',
                                     'cancelled' => 'bg-secondary',
                                     'draft'     => 'bg-warning text-dark',
                                 default     => 'bg-secondary',
                                };
                             ?>

    <span class="badge <?= $badgeClass ?>">
        <?= esc(ucfirst($status)) ?>
    </span>
</td>
                        
                    </tr>
                <?php endforeach; ?>

                <?php if ($latest === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No invoices yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
