<?php
/**
 * One editable line-item row.
 *
 * @var string|int $i        row index ("__i__" in the JS template)
 * @var array      $item     description, qty, unit_price, tax_percent, product_id
 * @var array      $products the user's products (for the picker)
 */
$field = static fn (string $k, string $d = '') => esc((string) ($item[$k] ?? $d), 'attr');
?>
<tr>
    <td>
        <select class="form-select form-select-sm js-product" name="items[<?= $i ?>][product_id]">
            <option value="">Custom</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"
                        data-name="<?= esc($p['name'], 'attr') ?>"
                        data-price="<?= esc($p['unit_price'], 'attr') ?>"
                        data-tax="<?= esc($p['tax_percent'], 'attr') ?>"
                        <?= (string) ($item['product_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td class="col-desc"><input class="form-control form-control-sm js-desc" name="items[<?= $i ?>][description]" value="<?= $field('description') ?>" maxlength="255" required></td>
    <td><input class="form-control form-control-sm js-qty" type="number" step="0.01" min="0.01" name="items[<?= $i ?>][qty]" value="<?= $field('qty', '1') ?>" required></td>
    <td><input class="form-control form-control-sm js-price" type="number" step="0.01" min="0" name="items[<?= $i ?>][unit_price]" value="<?= $field('unit_price', '0') ?>" required></td>
    <td><input class="form-control form-control-sm js-tax" type="number" step="0.01" min="0" max="100" name="items[<?= $i ?>][tax_percent]" value="<?= $field('tax_percent', '0') ?>"></td>
    <td class="line-total js-line">0.00</td>
    <td><button type="button" class="btn btn-sm btn-outline-danger js-remove" title="Remove line">&times;</button></td>
</tr>
