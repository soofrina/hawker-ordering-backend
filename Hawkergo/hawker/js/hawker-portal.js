(function () {
  'use strict';

  var STALL_COLORS = ['#f5b800', '#ff8c00', '#e0a600'];

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function buildAnimatedStallName(name, size) {
    var chars = String(name).split('');
    var html = '<span class="hg-hawker-stall-name-animated hg-hawker-stall-name-animated--' + size + '">';
    var index = 0;

    chars.forEach(function (char) {
      if (char === ' ') {
        html += '<span class="hg-hawker-stall-letter hg-hawker-stall-letter--space">&nbsp;</span>';
        return;
      }
      var color = STALL_COLORS[index % STALL_COLORS.length];
      html += '<span class="hg-hawker-stall-letter" style="--i:' + index + ';--c:' + color + ';">'
        + escapeHtml(char) + '</span>';
      index += 1;
    });

    html += '</span>';
    return html;
  }

  function showRotatorName(bodyEl, name) {
    bodyEl.classList.remove('is-visible');
    bodyEl.classList.add('is-exiting');

    window.setTimeout(function () {
      bodyEl.innerHTML = buildAnimatedStallName(name, 'rotator');
      bodyEl.classList.remove('is-exiting');
      bodyEl.classList.add('is-entering');

      window.requestAnimationFrame(function () {
        bodyEl.classList.add('is-visible');
        bodyEl.classList.remove('is-entering');
      });
    }, 280);
  }

  function initStallRotator() {
    var box = document.querySelector('.js-hawker-stall-rotator');
    if (!box) return;

    var names = [];
    try {
      names = JSON.parse(box.getAttribute('data-stalls') || '[]');
    } catch (e) {
      names = [];
    }
    if (!names.length) return;

    var bodyEl = box.querySelector('.js-hawker-stall-rotator-body');
    if (!bodyEl) return;

    var index = 0;
    showRotatorName(bodyEl, names[0]);

    if (names.length < 2) return;

    window.setInterval(function () {
      index = (index + 1) % names.length;
      showRotatorName(bodyEl, names[index]);
    }, 3200);
  }

  function buildPreviewPhoto(imageSrc, altText) {
    if (imageSrc) {
      return '<span class="hg-hawker-stall-preview-photo">'
        + '<img src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(altText || 'Stall') + '">'
        + '</span>';
    }
    return '<span class="hg-hawker-stall-preview-photo">'
      + '<span class="hg-hawker-stall-preview-photo-fallback"><i class="fa fa-store"></i></span>'
      + '</span>';
  }

  function initStallPicker() {
    var picker = document.querySelector('.js-hawker-stall-picker');
    var select = document.getElementById('rs_id');
    var preview = document.querySelector('.js-hawker-stall-preview');
    if (!picker || !select) return;

    function setSelection(rsId, stallName, stallImage) {
      select.value = String(rsId);
      picker.querySelectorAll('.hg-hawker-stall-pick-btn').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-rs-id') === String(rsId));
      });
      if (preview && stallName) {
        preview.innerHTML = '<div class="hg-hawker-stall-preview-inner">'
          + buildPreviewPhoto(stallImage, stallName)
          + '<div class="hg-hawker-stall-preview-name">' + buildAnimatedStallName(stallName, 'sm') + '</div>'
          + '</div>';
      }
    }

    picker.querySelectorAll('.hg-hawker-stall-pick-btn:not(.is-taken)').forEach(function (btn) {
      btn.addEventListener('click', function () {
        setSelection(
          btn.getAttribute('data-rs-id'),
          btn.getAttribute('data-stall-name'),
          btn.getAttribute('data-stall-image') || ''
        );
      });
    });

    select.addEventListener('change', function () {
      var option = select.options[select.selectedIndex];
      if (!select.value) {
        picker.querySelectorAll('.hg-hawker-stall-pick-btn').forEach(function (btn) {
          btn.classList.remove('is-active');
        });
        if (preview) preview.innerHTML = '';
        return;
      }
      var btn = picker.querySelector('.hg-hawker-stall-pick-btn[data-rs-id="' + select.value + '"]');
      var image = option ? (option.getAttribute('data-stall-image') || '') : '';
      if (btn && btn.getAttribute('data-stall-image')) {
        image = btn.getAttribute('data-stall-image');
      }
      setSelection(select.value, option ? option.textContent : '', image);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initStallRotator();
    initStallPicker();
  });
})();
