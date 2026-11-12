<?php if ($msg = session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($msg = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" role="alert"><?= esc($msg) ?></div>
<?php endif; ?>

<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            <?php foreach ((array) $errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
