(function () {
  var banner = document.querySelector('[data-popup-store]');
  if (!banner) return;

  var root = document.documentElement;
  var closeAt = parseInt(banner.getAttribute('data-popup-store-close'), 10);
  var daysEl = banner.querySelector('[data-popup-store-days]');
  var hoursEl = banner.querySelector('[data-popup-store-hours]');
  var minutesEl = banner.querySelector('[data-popup-store-minutes]');
  var secondsEl = banner.querySelector('[data-popup-store-seconds]');
  var intervalId = null;

  var pad = function (value) {
    return String(value).padStart(2, '0');
  };

  var syncHeight = function () {
    root.style.setProperty('--btx-popup-store-height', banner.offsetHeight + 'px');
  };

  var tick = function () {
    var remaining = Math.max(closeAt - Date.now(), 0);
    var totalSeconds = Math.floor(remaining / 1000);
    var days = Math.floor(totalSeconds / 86400);
    var hours = Math.floor((totalSeconds % 86400) / 3600);
    var minutes = Math.floor((totalSeconds % 3600) / 60);
    var seconds = totalSeconds % 60;

    daysEl.textContent = String(days);
    hoursEl.textContent = pad(hours);
    minutesEl.textContent = pad(minutes);
    secondsEl.textContent = pad(seconds);

    if (remaining <= 0 && intervalId) {
      clearInterval(intervalId);
    }
  };

  syncHeight();
  tick();
  intervalId = window.setInterval(tick, 1000);
  window.addEventListener('resize', syncHeight);

  if ('ResizeObserver' in window) {
    new ResizeObserver(syncHeight).observe(banner);
  }
}());
