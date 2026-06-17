(function () {
  function formatMoney(amount) {
    return '$' + amount.toFixed(2);
  }

  function clampQty(value) {
    var qty = parseInt(value, 10);
    if (isNaN(qty) || qty < 1) return 1;
    if (qty > 99) return 99;
    return qty;
  }

  function updateCartTotals(root) {
    var scope = root || document;
    var total = 0;
    scope.querySelectorAll('.js-cart-row').forEach(function (row) {
      var price = parseFloat(row.getAttribute('data-price')) || 0;
      var input = row.querySelector('.js-cart-qty');
      var qty = clampQty(input ? input.value : 1);
      if (input) input.value = qty;
      var lineTotal = price * qty;
      total += lineTotal;
      var subtotalEl = row.querySelector('.js-row-subtotal');
      if (subtotalEl) subtotalEl.textContent = formatMoney(lineTotal);
    });

    scope.querySelectorAll('[data-cart-total]').forEach(function (el) {
      el.textContent = formatMoney(total);
    });

    ['cart-subtotal', 'cart-total', 'pay-amount', 'stall-cart-total'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = formatMoney(total);
    });
  }

  function collectQtyData(panel) {
    var data = new FormData();
    panel.querySelectorAll('.js-cart-qty').forEach(function (input) {
      if (input.name) data.append(input.name, clampQty(input.value));
    });
    return data;
  }

  function syncCartToServer(panel) {
    var url = panel.getAttribute('data-cart-sync');
    if (!url) return;
    panel._pendingSync = false;
    fetch(url, { method: 'POST', body: collectQtyData(panel), credentials: 'same-origin', keepalive: true });
  }

  function autoSaveCart(scope) {
    if (!scope || !scope.closest) return;

    var formPanel = scope.closest('.js-auto-save-cart');
    if (formPanel) {
      clearTimeout(formPanel._saveTimer);
      formPanel._saveTimer = setTimeout(function () {
        formPanel.submit();
      }, 350);
      return;
    }

    var syncPanel = scope.closest('[data-cart-sync]');
    if (!syncPanel) return;
    syncPanel._pendingSync = true;
    clearTimeout(syncPanel._saveTimer);
    syncPanel._saveTimer = setTimeout(function () {
      syncCartToServer(syncPanel);
    }, 250);
  }

  // Flush unsaved quantity changes if the user navigates away mid-debounce.
  window.addEventListener('pagehide', function () {
    document.querySelectorAll('[data-cart-sync]').forEach(function (panel) {
      if (!panel._pendingSync) return;
      clearTimeout(panel._saveTimer);
      panel._pendingSync = false;
      var url = panel.getAttribute('data-cart-sync');
      if (url && navigator.sendBeacon) {
        navigator.sendBeacon(url, collectQtyData(panel));
      }
    });
  });

  function bindQtyControls(scope) {
    scope = scope || document;
    scope.querySelectorAll('.js-cart-qty').forEach(function (input) {
      if (input.dataset.bound) return;
      input.dataset.bound = '1';
      input.addEventListener('change', function () {
        var panel = scope.closest('.js-cart-panel') || document;
        updateCartTotals(panel);
        autoSaveCart(input);
      });
      input.addEventListener('input', function () {
        updateCartTotals(scope.closest('.js-cart-panel') || document);
      });
    });

    scope.querySelectorAll('.js-qty-minus').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', function () {
        var input = btn.parentElement.querySelector('.js-cart-qty');
        if (!input) return;
        input.value = clampQty(parseInt(input.value, 10) - 1);
        var panel = scope.closest('.js-cart-panel') || document;
        updateCartTotals(panel);
        autoSaveCart(btn);
      });
    });

    scope.querySelectorAll('.js-qty-plus').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', function () {
        var input = btn.parentElement.querySelector('.js-cart-qty');
        if (!input) return;
        input.value = clampQty(parseInt(input.value, 10) + 1);
        var panel = scope.closest('.js-cart-panel') || document;
        updateCartTotals(panel);
        autoSaveCart(btn);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-cart-panel').forEach(function (panel) {
      bindQtyControls(panel);
      updateCartTotals(panel);
    });
    bindQtyControls(document);
    updateCartTotals(document);
  });

  window.hgCartQty = {
    bind: bindQtyControls,
    updateTotals: updateCartTotals,
    clampQty: clampQty,
    formatMoney: formatMoney
  };
})();
