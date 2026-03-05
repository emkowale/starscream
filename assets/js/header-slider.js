document.addEventListener('DOMContentLoaded', function () {
  var sliders = document.querySelectorAll('[data-btx-header-slider]');
  if (!sliders.length) return;

  sliders.forEach(function (slider) {
    var slides = Array.prototype.slice.call(slider.querySelectorAll('.btx-header-slider__slide'));
    var dots = Array.prototype.slice.call(slider.querySelectorAll('.btx-header-slider__dot'));
    var ring = slider.querySelector('[data-slider-progress]');
    var percentNode = slider.querySelector('[data-slider-percent]');
    if (!slides.length) return;

    var total = slides.length;

    var autoplayMs = parseInt(slider.getAttribute('data-autoplay-ms') || '10000', 10);
    if (!Number.isFinite(autoplayMs)) autoplayMs = 10000;
    autoplayMs = Math.max(3000, Math.min(30000, autoplayMs));
    if (total < 2) autoplayMs = 0;

    var current = 0;
    var rafId = 0;
    var startAt = performance.now();

    var radius = ring ? parseFloat(ring.getAttribute('r') || '0') : 0;
    var circumference = radius > 0 ? 2 * Math.PI * radius : 0;
    if (ring && circumference > 0) {
      ring.style.strokeDasharray = String(circumference);
      ring.style.strokeDashoffset = String(circumference);
    }

    var setProgress = function (progress) {
      var clamped = Math.max(0, Math.min(1, progress));
      if (ring && circumference > 0) {
        ring.style.strokeDashoffset = String(circumference * (1 - clamped));
      }
      if (percentNode) percentNode.textContent = String(Math.round(clamped * 100)) + '%';
    };

    var resetClock = function () {
      startAt = performance.now();
      setProgress(0);
    };

    var setActive = function (index, shouldResetClock) {
      current = ((index % total) + total) % total;

      slides.forEach(function (slide, slideIndex) {
        var isActive = slideIndex === current;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });

      dots.forEach(function (dot, dotIndex) {
        var isActive = dotIndex === current;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      if (shouldResetClock) resetClock();
    };

    var tick = function (now) {
      if (autoplayMs > 0) {
        var elapsed = now - startAt;
        var progress = elapsed / autoplayMs;

        if (progress >= 1) {
          setActive(current + 1, false);
          resetClock();
        } else {
          setProgress(progress);
        }
      }
      rafId = window.requestAnimationFrame(tick);
    };

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var target = parseInt(dot.getAttribute('data-slide-target') || '0', 10);
        if (!Number.isFinite(target)) return;
        setActive(target, true);
        if (autoplayMs === 0) setProgress(1);
      });
    });

    setActive(0, false);
    if (autoplayMs === 0) {
      setProgress(1);
    } else {
      resetClock();
      rafId = window.requestAnimationFrame(tick);
    }

  });
});
