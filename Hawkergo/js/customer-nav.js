(function () {
  if (typeof Headroom === 'undefined') {
    return;
  }

  var header = document.querySelector('#header');
  if (!header || header.dataset.headroomReady) {
    return;
  }

  header.dataset.headroomReady = '1';
  var headroom = new Headroom(header, {
    offset: 80,
    tolerance: 40,
    classes: {
      initial: 'animated',
      pinned: 'fadeInDown',
      unpinned: 'fadeOutUp'
    }
  });
  headroom.init();
})();
