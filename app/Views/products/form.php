<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $v = static fn (string $k, string $d = '') => esc(old($k, $row[$k] ?? $d, false)); ?>
<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="post" action="<?= esc($action, 'attr') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="name">Name *</label>
                <input class="form-control" id="name" name="name" value="<?= $v('name') ?>" maxlength="150" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"><?= $v('description') ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="unit_price">Unit price *</label>
                    <input class="form-control" type="number" step="0.01" min="0" id="unit_price" name="unit_price" value="<?= $v('unit_price', '0.00') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="tax_percent">Tax % *</label>
                    <input class="form-control" type="number" step="0.01" min="0" max="100" id="tax_percent" name="tax_percent" value="<?= $v('tax_percent', '0.00') ?>" required>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-link" href="<?= site_url('products') ?>">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
