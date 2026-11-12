<?php
$links = [
    ['dashboard', 'Dashboard', 'dashboard*'],
    ['invoices', 'Invoices', 'invoices*'],
    ['customers', 'Customers', 'customers*'],
    ['products', 'Products', 'products*'],
    ['settings', 'Settings', 'settings*'],
];
?>
<nav class="app-sidebar p-3 d-none d-md-block">
    <div class="brand fs-5 mb-4 px-2">Invoice App</div>
    <ul class="nav flex-column gap-1">
        <?php foreach ($links as [$path, $label, $pattern]): ?>
            <li class="nav-item">
                <a class="nav-link <?= url_is($pattern) ? 'active' : '' ?>" href="<?= site_url($path) ?>"><?= esc($label) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
