<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form class="d-flex gap-2" method="get" action="<?= site_url('customers') ?>">
        <input class="form-control" type="search" name="q" value="<?= esc($q) ?>" placeholder="Search name or email">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
    </form>
    <a class="btn btn-primary" href="<?= site_url('customers/create') ?>">Add customer</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Tax ID</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= esc($row['name']) ?></td>
                    <td><?= esc($row['email']) ?></td>
                    <td><?= esc($row['phone']) ?></td>
                    <td><?= esc($row['tax_id']) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('customers/edit/' . $row['id']) ?>">Edit</a>
                        <form class="d-inline" method="post" action="<?= site_url('customers/delete/' . $row['id']) ?>"
                              onsubmit="return confirm('Delete this customer? Existing invoices are kept.');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No customers found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?= $pager->links() ?></div>
<?= $this->endSection() ?>
