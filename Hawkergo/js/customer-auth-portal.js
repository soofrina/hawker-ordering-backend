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
    var html = '<span class="hg-customer-feature-animated">';
    var index = 0;
    String(text).split('').forEach(function (char) {
      if (char === ' ') {
        html += '<span class="hg-customer-feature-letter hg-customer-feature-letter--space">&nbsp;</span>';
        return;
      }
      var color = COLORS[index % COLORS.length];
      html += '<span class="hg-customer-feature-letter" style="--i:' + index + ';--c:' + color + ';">'
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
    var box = document.querySelector('.js-customer-feature-rotator');
    if (!box) return;

    var labels = [];
    try {
      labels = JSON.parse(box.getAttribute('data-labels') || '[]');
    } catch (e) {
      labels = [];
    }
    if (!labels.length) return;

    var bodyEl = box.querySelector('.js-customer-feature-rotator-body');
    if (!bodyEl) return;

    var index = 0;
    showFeature(bodyEl, labels[0]);

    if (labels.length < 2) return;

    window.setInterval(function () {
      index = (index + 1) % labels.length;
      showFeature(bodyEl, labels[index]);
    }, 3200);
  }

  document.addEventListener('DOMContentLoaded', initFeatureRotator);
})();
