<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h4 mb-1">Invoice App</h1>
        <p class="text-muted">Sign in to continue</p>

        <?= $this->include('layouts/_flash') ?>

        <form method="post" action="<?= site_url('login') ?>" autocomplete="on">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= esc(old('email', '', false)) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Sign in</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
