/**
 * Invoice form: dynamic line items + live totals.
 * The server recalculates everything on save; these numbers are display only.
 * Rounding mirrors App\Libraries\InvoiceCalculator.
 */
(function () {
  'use strict';

  var body = document.getElementById('items-body');
  var tpl = document.getElementById('row-template');
  var discountEl = document.getElementById('discount');
  if (!body || !tpl) { return; }

  var nextIndex = parseInt(body.getAttribute('data-next-index'), 10) || 0;

  function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
  function round2(n) { return Math.round((n + Number.EPSILON) * 100) / 100; }
  function fmt(n) { return round2(n).toFixed(2); }

  function recalc() {
    var subtotal = 0;
    var taxTotal = 0;

    Array.prototype.forEach.call(body.rows, function (tr) {
      var qty = Math.max(0, num(tr.querySelector('.js-qty').value));
      var price = Math.max(0, num(tr.querySelector('.js-price').value));
      var rate = Math.min(100, Math.max(0, num(tr.querySelector('.js-tax').value)));
      var line = round2(qty * price);
      subtotal += line;
      taxTotal += line * rate / 100;
      tr.querySelector('.js-line').textContent = fmt(line);
    });

    var discount = Math.min(Math.max(num(discountEl.value), 0), subtotal);
    var grand = subtotal - discount + taxTotal;

    document.getElementById('t-subtotal').textContent = fmt(subtotal);
    document.getElementById('t-discount').textContent = fmt(discount);
    document.getElementById('t-tax').textContent = fmt(taxTotal);
    document.getElementById('t-grand').textContent = fmt(grand);
  }

  function addRow() {
    body.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, String(nextIndex++)));
    recalc();
  }

  body.addEventListener('input', recalc);

  body.addEventListener('change', function (e) {
    if (!e.target.classList.contains('js-product')) { return; }
    var opt = e.target.options[e.target.selectedIndex];
    var tr = e.target.closest('tr');
    if (opt && opt.value) {
      tr.querySelector('.js-desc').value = opt.getAttribute('data-name');
      tr.querySelector('.js-price').value = opt.getAttribute('data-price');
      tr.querySelector('.js-tax').value = opt.getAttribute('data-tax');
    }
    recalc();
  });

  body.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-remove');
    if (!btn) { return; }
    if (body.rows.length > 1) { btn.closest('tr').remove(); }
    recalc();
  });

  document.getElementById('add-row').addEventListener('click', addRow);
  discountEl.addEventListener('input', recalc);

  if (body.rows.length === 0) { addRow(); } else { recalc(); }
})();
