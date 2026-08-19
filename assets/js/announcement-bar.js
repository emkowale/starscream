(function () {
  'use strict';

  var bars = document.querySelectorAll('[data-announcement-bar]');
  if (!bars.length) return;

  bars.forEach(function (bar) {
    var pause = function () { bar.classList.add('is-paused'); };
    var resume = function () { bar.classList.remove('is-paused'); };

    bar.addEventListener('pointerdown', function (event) {
      if (event.pointerType === 'touch' || event.pointerType === 'pen') pause();
    }, { passive: true });
    bar.addEventListener('pointerup', resume, { passive: true });
    bar.addEventListener('pointercancel', resume, { passive: true });
    bar.addEventListener('pointerleave', resume, { passive: true });

    bar.addEventListener('touchstart', pause, { passive: true });
    bar.addEventListener('touchend', resume, { passive: true });
    bar.addEventListener('touchcancel', resume, { passive: true });
  });
})();
