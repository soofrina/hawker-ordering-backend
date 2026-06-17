(function ($) {
  'use strict';

  function openModal(id) {
    if (!id || !$('#' + id).length) {
      return;
    }
    $('#' + id).modal('show');
  }

  $(function () {
    if (window.HG_OPEN_MODAL) {
      openModal(window.HG_OPEN_MODAL);
    }

    $('.step-clickable').on('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('click');
      }
    });

    $('[data-dismiss="modal"][data-toggle="modal"]').on('click', function () {
      var target = $(this).data('target');
      if (!target) {
        return;
      }
      var $current = $(this).closest('.modal');
      $current.one('hidden.bs.modal', function () {
        openModal(target.replace('#', ''));
      });
    });

    var modalOrderLabels = { 'dine-in': 'Dine-In', 'takeaway': 'Take-Away' };
    $('.js-modal-order-type').on('change', function () {
      var val = $('.js-modal-order-type:checked').val();
      $('#modal-order-type-label').text(modalOrderLabels[val] || val);
    });

    $('.js-cat-filter').on('click', function (e) {
      var filter = $(this).data('filter');
      if (!filter) {
        return;
      }
      e.preventDefault();
      var $listing = $('.hawkerstall-listing');
      if ($listing.length && typeof $listing.isotope === 'function') {
        $listing.isotope({ filter: filter });
        $('.hg-filter-pills a').removeClass('selected');
        $('.hg-filter-pills a[data-filter="' + filter + '"]').addClass('selected');
      }
      var $target = $('#stalls');
      if ($target.length) {
        $('html, body').animate({ scrollTop: $target.offset().top - 80 }, 400);
      }
    });
  });
})(jQuery);
