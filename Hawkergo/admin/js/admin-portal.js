(function () {
  'use strict';

  var COLORS = ['#f5b800', '#ff8c00', '#e0a600'];

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function buildAnimatedLabel(text) {
    var html = '<span class="hg-admin-feature-animated">';
    var index = 0;
    String(text).split('').forEach(function (char) {
      if (char === ' ') {
        html += '<span class="hg-admin-feature-letter hg-admin-feature-letter--space">&nbsp;</span>';
        return;
      }
      var color = COLORS[index % COLORS.length];
      html += '<span class="hg-admin-feature-letter" style="--i:' + index + ';--c:' + color + ';">'
        + escapeHtml(char) + '</span>';
      index += 1;
    });
    html += '</span>';
    return html;
  }

  function showFeature(bodyEl, text) {
    bodyEl.classList.remove('is-visible');
    bodyEl.classList.add('is-exiting');
    window.setTimeout(function () {
      bodyEl.innerHTML = buildAnimatedLabel(text);
      bodyEl.classList.remove('is-exiting');
      bodyEl.classList.add('is-entering');
      window.requestAnimationFrame(function () {
        bodyEl.classList.add('is-visible');
        bodyEl.classList.remove('is-entering');
      });
    }, 280);
  }

  function initFeatureRotator() {
    var box = document.querySelector('.js-admin-feature-rotator');
    if (!box) return;

    var labels = [];
    try {
      labels = JSON.parse(box.getAttribute('data-labels') || '[]');
    } catch (e) {
      labels = [];
    }
    if (!labels.length) return;

    var bodyEl = box.querySelector('.js-admin-feature-rotator-body');
    if (!bodyEl) return;

    var index = 0;
    showFeature(bodyEl, labels[0]);

    if (labels.length < 2) return;

    window.setInterval(function () {
      index = (index + 1) % labels.length;
      showFeature(bodyEl, labels[index]);
    }, 3200);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFeatureRotator();
    injectAdminBrand();
  });

  function injectAdminBrand() {
    var link = document.querySelector('.navbar-header .navbar-brand, .navbar-brand');
    if (!link || link.querySelector('.hg-admin-brand-showcase')) return;
    if (document.body.classList.contains('hg-admin-login-body')) return;

    var letters = [
      { c: 'A', color: '#f5b800' },
      { c: 'd', color: '#ff8c00' },
      { c: 'm', color: '#e0a600' },
      { c: 'i', color: '#f5b800' },
      { c: 'n', color: '#ff8c00' }
    ];
    var html = '<span class="hg-admin-brand-showcase hg-admin-brand-showcase--sm">';
    html += '<span class="hg-admin-brand-mark" aria-hidden="true"><i class="fa fa-shield"></i><span class="hg-admin-brand-ring"></span></span>';
    html += '<span class="hg-admin-brand-text"><span class="hg-admin-brand-word">';
    letters.forEach(function (letter, i) {
      html += '<span class="hg-admin-brand-letter" style="--i:' + i + ';--c:' + letter.color + ';">' + letter.c + '</span>';
    });
    html += '</span><span class="hg-admin-brand-sub">HawkerGo Control</span></span></span>';

    link.innerHTML = html;
    link.classList.add('hg-admin-brand-link');
    if (!link.getAttribute('href')) {
      link.setAttribute('href', 'dashboard.php');
    }
  }
})();
