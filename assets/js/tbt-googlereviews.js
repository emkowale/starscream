(function () {
  var widgets = document.querySelectorAll('[data-btx-google-reviews]');
  if (!widgets.length) return;

  widgets.forEach(function (widget) {
    var slides = Array.prototype.slice.call(widget.querySelectorAll('.testimonials__testimonial'));
    var dots = Array.prototype.slice.call(widget.querySelectorAll('[data-review-dot]'));
    if (slides.length < 2 || !dots.length) return;

    var autoplayMs = parseInt(widget.getAttribute('data-autoplay-ms') || '0', 10);
    if (!isFinite(autoplayMs) || autoplayMs < 0) autoplayMs = 0;

    var activeIndex = 0;
    var timer = null;

    var setActive = function (nextIndex) {
      if (!slides.length) return;
      if (nextIndex < 0) nextIndex = slides.length - 1;
      if (nextIndex >= slides.length) nextIndex = 0;
      activeIndex = nextIndex;

      slides.forEach(function (slide, idx) {
        var isActive = idx === activeIndex;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });

      dots.forEach(function (dot, idx) {
        var isActive = idx === activeIndex;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        dot.setAttribute('tabindex', isActive ? '0' : '-1');
        var parent = dot.closest('.dot');
        if (parent) parent.classList.toggle('is-selected', isActive);
      });
    };

    var stopAutoplay = function () {
      if (!timer) return;
      window.clearInterval(timer);
      timer = null;
    };

    var startAutoplay = function () {
      stopAutoplay();
      if (!autoplayMs || autoplayMs < 1000) return;
      timer = window.setInterval(function () {
        setActive(activeIndex + 1);
      }, autoplayMs);
    };

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-review-dot') || '0', 10);
        if (!isFinite(idx)) idx = 0;
        setActive(idx);
        startAutoplay();
      });

      dot.addEventListener('keydown', function (event) {
        if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
        event.preventDefault();
        setActive(event.key === 'ArrowRight' ? activeIndex + 1 : activeIndex - 1);
        startAutoplay();
      });
    });

    widget.addEventListener('mouseenter', stopAutoplay);
    widget.addEventListener('mouseleave', startAutoplay);
    widget.addEventListener('focusin', stopAutoplay);
    widget.addEventListener('focusout', function () {
      if (widget.contains(document.activeElement)) return;
      startAutoplay();
    });

    setActive(0);
    startAutoplay();
  });
})();
