<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $v = static fn (string $k) => esc(old($k, $row[$k] ?? '', false)); ?>
<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="post" action="<?= esc($action, 'attr') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="name">Name *</label>
                <input class="form-control" id="name" name="name" value="<?= $v('name') ?>" maxlength="150" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" value="<?= $v('email') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" id="phone" name="phone" value="<?= $v('phone') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="tax_id">GSTIN / Tax ID</label>
                <input class="form-control" id="tax_id" name="tax_id" value="<?= $v('tax_id') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?= $v('address') ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-link" href="<?= site_url('customers') ?>">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
