<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Invoice App') ?> · Invoice App</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="d-flex">
    <?= $this->include('layouts/_sidebar') ?>

    <div class="app-main flex-grow-1">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h1 class="h5 mb-0"><?= esc($title ?? '') ?></h1>
            <div class="text-muted small">
                <?= esc((string) session('user_name')) ?>
                · <a href="<?= site_url('logout') ?>">Sign out</a>
            </div>
        </header>

        <main class="p-4">
            <?= $this->include('layouts/_flash') ?>
            <?= $this->renderSection('content') ?>
        </main>

        <footer class="px-4 pb-4 text-muted small">&copy; <?= date('Y') ?> Invoice App</footer>
    </div>
</div>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
