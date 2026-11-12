<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$val = static fn (string $k, string $d = '') => esc((string) old($k, $d, false), 'attr');

// Rows: re-posted input (after a validation error) > saved items > one blank row.
$oldItems = old('items', null, false);
$rows     = is_array($oldItems) ? array_values($oldItems) : ($items !== [] ? $items : [[]]);

$selectedCustomer = (string) old('customer_id', $invoice['customer_id'] ?? '', false);
?>
<form method="post" action="<?= esc($action, 'attr') ?>">
    <?= csrf_field() ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label" for="customer_id">Customer *</label>
                    <select class="form-select" id="customer_id" name="customer_id" required>
                        <option value="">Select customer</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $selectedCustomer === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($customers === []): ?>
                        <div class="form-text">No customers yet. <a href="<?= site_url('customers/create') ?>">Add one first</a>.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="issue_date">Issue date *</label>
                    <input class="form-control" type="date" id="issue_date" name="issue_date" value="<?= $val('issue_date', $invoice['issue_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="due_date">Due date *</label>
                    <input class="form-control" type="date" id="due_date" name="due_date" value="<?= $val('due_date', $invoice['due_date'] ?? date('Y-m-d', strtotime('+14 days'))) ?>" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Line items</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">Add line</button>
        </div>
        <div class="table-responsive">
            <table class="table table-items align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:18%">Product</th><th>Description</th><th style="width:9%">Qty</th>
                        <th style="width:12%">Unit price</th><th style="width:9%">Tax %</th>
                        <th class="text-end" style="width:12%">Line total</th><th style="width:4%"></th>
                    </tr>
                </thead>
                <tbody id="items-body" data-next-index="<?= count($rows) ?>">
                    <?php foreach ($rows as $i => $item): ?>
                        <?= view('invoices/_item_row', ['i' => $i, 'item' => $item, 'products' => $products]) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <label class="form-label" for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="4"><?= esc((string) old('notes', $invoice['notes'] ?? '', false)) ?></textarea>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm"><div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="discount">Discount (amount)</label>
                    <input class="form-control" type="number" step="0.01" min="0" id="discount" name="discount" value="<?= $val('discount', (string) ($invoice['discount'] ?? '0')) ?>">
                </div>
                <dl class="row mb-0">
                    <dt class="col-6 fw-normal">Subtotal</dt><dd class="col-6 text-end" id="t-subtotal">0.00</dd>
                    <dt class="col-6 fw-normal">Discount</dt><dd class="col-6 text-end" id="t-discount">0.00</dd>
                    <dt class="col-6 fw-normal">Tax</dt><dd class="col-6 text-end" id="t-tax">0.00</dd>
                    <dt class="col-6">Grand total</dt><dd class="col-6 text-end fw-bold" id="t-grand">0.00</dd>
                </dl>
                <div class="form-text">Preview only. Totals are recalculated on the server when you save.</div>
            </div></div>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" type="submit">Save invoice</button>
        <a class="btn btn-link" href="<?= site_url('invoices') ?>">Cancel</a>
    </div>
</form>

<template id="row-template">
    <?= view('invoices/_item_row', ['i' => '__i__', 'item' => [], 'products' => $products]) ?>
</template>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/invoice-form.js') ?>"></script>
<?= $this->endSection() ?>
