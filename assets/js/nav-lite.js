document.addEventListener('DOMContentLoaded', function () {
  var headerBar = document.querySelector('.btx-header-bar');
  var injectedWrap = document.getElementById('starscream-injected-nav');
  var injectedNav  = injectedWrap ? injectedWrap.querySelector('.site-nav') : null;

  // If header exists and doesn't already contain a .site-nav, move injected nav into it.
  if (headerBar && injectedNav && !headerBar.querySelector('.site-nav')) {
    var icons = headerBar.querySelector('.header-icons');
    headerBar.insertBefore(injectedNav, icons || null);
  }

  // Determine the nav we will control (prefer header one, fallback to injected).
  var nav = (headerBar && headerBar.querySelector('.site-nav')) ? headerBar.querySelector('.site-nav') : injectedNav;
  if (!nav) return;

  // No menu items? Skip building a toggle so the mobile hamburger stays hidden.
  if (!nav.querySelector('li')) return;

  // Ensure it has an id for aria-controls.
  if (!nav.id) nav.id = 'primary-menu';

  // If header is present, create a hamburger before nav; otherwise leave nav as-is.
  if (headerBar) {
    headerBar.classList.add('has-nav-toggle');
    var btn = document.createElement('button');
    btn.className = 'btx-nav-toggle';
    btn.setAttribute('aria-controls', nav.id);
    btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML =
      '<span class="btx-nav-bar"></span>' +
      '<span class="btx-nav-bar"></span>' +
      '<span class="btx-nav-bar"></span>' +
      '<span class="screen-reader-text">Toggle navigation</span>';
    headerBar.insertBefore(btn, nav);

    btn.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
});
