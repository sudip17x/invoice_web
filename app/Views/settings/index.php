<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $v = static fn (string $k) => esc((string) old($k, $row[$k] ?? '', false)); ?>
<div class="card shadow-sm" style="max-width: 820px;">
    <div class="card-body">
        <form method="post" action="<?= site_url('settings') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label" for="company_name">Company name *</label>
                    <input class="form-control" id="company_name" name="company_name" value="<?= $v('company_name') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="invoice_prefix">Invoice prefix *</label>
                    <input class="form-control" id="invoice_prefix" name="invoice_prefix" value="<?= $v('invoice_prefix') ?>" maxlength="10" pattern="[A-Za-z0-9]+" required>
                    <div class="form-text">Letters and digits only, e.g. INV &rarr; INV-<?= date('Y') ?>-0001</div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?= $v('address') ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" id="phone" name="phone" value="<?= $v('phone') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" value="<?= $v('email') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="tax_id">GSTIN / Tax ID</label>
                    <input class="form-control" id="tax_id" name="tax_id" value="<?= $v('tax_id') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="bank_details">Bank details</label>
                <textarea class="form-control" id="bank_details" name="bank_details" rows="3"><?= $v('bank_details') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="footer_note">Footer note</label>
                <textarea class="form-control" id="footer_note" name="footer_note" rows="2"><?= $v('footer_note') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="logo">Logo (JPG/PNG, max 1 MB)</label>
                <?php if (! empty($row['logo_path'])): ?>
                    <div class="mb-2"><img src="<?= site_url('settings/logo') ?>" alt="Current logo" style="max-height:60px"></div>
                <?php endif; ?>
                <input class="form-control" type="file" id="logo" name="logo" accept="image/png,image/jpeg">
            </div>
            <button class="btn btn-primary" type="submit">Save settings</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
