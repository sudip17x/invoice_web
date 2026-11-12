<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $base = site_url('invoices'); ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= $base ?>">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= esc($s, 'attr') ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="customer_id">Customer</label>
                <select class="form-select" id="customer_id" name="customer_id">
                    <option value="">All</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (string) $filters['customer_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="from">Issued from</label>
                <input class="form-control" type="date" id="from" name="from" value="<?= esc($filters['from'], 'attr') ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="to">Issued to</label>
                <input class="form-control" type="date" id="to" name="to" value="<?= esc($filters['to'], 'attr') ?>">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="<?= $base ?>">Reset</a>
                <a class="btn btn-primary ms-auto" href="<?= site_url('invoices/create') ?>">New invoice</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><?= sort_link('invoice_no', 'No.', $filters, $base) ?></th>
                    <th><?= sort_link('customer_name', 'Customer', $filters, $base) ?></th>
                    <th><?= sort_link('issue_date', 'Issued', $filters, $base) ?></th>
                    <th><?= sort_link('due_date', 'Due', $filters, $base) ?></th>
                    <th class="text-end"><?= sort_link('grand_total', 'Total', $filters, $base) ?></th>
                    <th><?= sort_link('status', 'Status', $filters, $base) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $inv): ?>
                <tr>
                    <td><a href="<?= site_url('invoices/view/' . $inv['id']) ?>"><?= esc($inv['invoice_no']) ?></a></td>
                    <td><?= esc($inv['customer_name']) ?></td>
                    <td><?= esc($inv['issue_date']) ?></td>
                    <td><?= esc($inv['due_date']) ?></td>
                    <td class="text-end"><?= money($inv['grand_total']) ?></td>
                    <td><?= status_badge($inv) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('invoices/view/' . $inv['id']) ?>">View</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('invoices/pdf/' . $inv['id']) ?>">PDF</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No invoices match.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?= $pager->links() ?></div>
<?= $this->endSection() ?>
