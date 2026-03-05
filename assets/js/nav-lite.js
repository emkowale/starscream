document.addEventListener('DOMContentLoaded', function () {
  var siteHeader = document.querySelector('.btx-site-header');
  var announcementBar = document.querySelector('[data-announcement-bar]');
  var headerSlider = document.querySelector('.btx-header-slider');
  var isHomeLikePage =
    document.body.classList.contains('home') ||
    document.body.classList.contains('front-page') ||
    document.body.classList.contains('btx-has-header-slider');
  var transparentHeaderEnabled = document.body.classList.contains('btx-transparent-header-enabled');
  var scrollThreshold = 0;
  var scrollTicking = false;
  var root = document.documentElement;
  var mobileQuery = window.matchMedia('(max-width: 980px)');
  var nav = null;
  var navToggleButton = null;
  var submenuIdSeed = 0;

  var recalcLayoutVars = function () {
    var announcementHeight = announcementBar ? announcementBar.offsetHeight : 0;
    root.style.setProperty('--btx-announcement-height', announcementHeight + 'px');

    var sliderAnnouncementOffset = 0;
    if (announcementBar && headerSlider) {
      var barRect = announcementBar.getBoundingClientRect();
      var sliderRect = headerSlider.getBoundingClientRect();
      var barBottomDoc = barRect.top + window.scrollY + announcementHeight;
      var sliderTopDoc = sliderRect.top + window.scrollY;
      sliderAnnouncementOffset = Math.max(Math.round(barBottomDoc - sliderTopDoc), 0);
    }
    root.style.setProperty('--btx-slider-announcement-offset', sliderAnnouncementOffset + 'px');

    var headerHeight = siteHeader ? siteHeader.offsetHeight : 0;
    root.style.setProperty('--btx-header-height', headerHeight + 'px');

    scrollThreshold = announcementHeight;
  };

  var syncHeaderFill = function () {
    if (!siteHeader) return;
    var announcementHeight = announcementBar ? announcementBar.offsetHeight : 0;
    var topOffset = Math.max(announcementHeight - window.scrollY, 0);
    root.style.setProperty('--btx-header-top-offset', topOffset + 'px');

    if (mobileQuery.matches && document.body.classList.contains('btx-mobile-menu-open')) {
      siteHeader.classList.add('is-scrolled');
      return;
    }

    if (!isHomeLikePage || !transparentHeaderEnabled) {
      siteHeader.classList.add('is-scrolled');
      return;
    }
    siteHeader.classList.toggle('is-scrolled', window.scrollY >= scrollThreshold && window.scrollY > 0);
  };

  var queueHeaderSync = function () {
    if (scrollTicking) return;
    scrollTicking = true;
    window.requestAnimationFrame(function () {
      syncHeaderFill();
      scrollTicking = false;
    });
  };

  var getDirectChildByClass = function (parent, className) {
    var i;
    for (i = 0; i < parent.children.length; i++) {
      if (parent.children[i].classList && parent.children[i].classList.contains(className)) {
        return parent.children[i];
      }
    }
    return null;
  };

  var getDirectLink = function (parent) {
    var i;
    for (i = 0; i < parent.children.length; i++) {
      if (parent.children[i].tagName === 'A') return parent.children[i];
    }
    return null;
  };

  var buildMobileMenuMeta = function () {
    if (!nav) return;
    var menu = nav.querySelector('.menu');
    if (!menu) return;

    var existing = menu.querySelector('li.btx-mobile-menu-meta');
    if (existing) existing.remove();
    if (!mobileQuery.matches) return;

    var accountSource = document.querySelector('.btx-header-bar .header-icons a[aria-label="Account"]');
    var phoneSource = document.querySelector('.btx-header-bar .header-icons a[href^="tel:"]');
    var emailSource = document.querySelector('.btx-header-bar .header-icons a[href^="mailto:"]');

    var rows = [];
    if (phoneSource && phoneSource.textContent.trim()) {
      rows.push({
        href: phoneSource.getAttribute('href'),
        iconClass: 'fas fa-phone',
        text: phoneSource.textContent.trim()
      });
    }
    if (emailSource && emailSource.textContent.trim()) {
      rows.push({
        href: emailSource.getAttribute('href'),
        iconClass: 'fas fa-envelope',
        text: emailSource.textContent.trim()
      });
    }

    rows.push({
      href: accountSource ? (accountSource.getAttribute('href') || '/my-account/') : '/my-account/',
      iconClass: 'fas fa-user',
      text: 'Account Login'
    });

    if (!rows.length) return;

    var metaItem = document.createElement('li');
    metaItem.className = 'btx-mobile-menu-meta';

    var list = document.createElement('div');
    list.className = 'btx-mobile-menu-meta-items';

    rows.forEach(function (row) {
      if (!row.href || !row.text) return;

      var link = document.createElement('a');
      link.className = 'btx-mobile-menu-meta-link';
      link.setAttribute('href', row.href);

      var iconWrap = document.createElement('span');
      iconWrap.className = 'btx-mobile-menu-meta-icon';
      iconWrap.setAttribute('aria-hidden', 'true');

      var icon = document.createElement('i');
      icon.className = row.iconClass;
      iconWrap.appendChild(icon);

      var text = document.createElement('span');
      text.className = 'btx-mobile-menu-meta-text';
      text.textContent = row.text;

      link.appendChild(iconWrap);
      link.appendChild(text);
      list.appendChild(link);
    });

    if (!list.children.length) return;

    metaItem.appendChild(list);
    menu.appendChild(metaItem);
  };

  var closeAllMobileSubmenus = function () {
    if (!nav) return;
    var openItems = nav.querySelectorAll('li.is-submenu-open');
    openItems.forEach(function (item) { item.classList.remove('is-submenu-open'); });
    var toggles = nav.querySelectorAll('.btx-submenu-toggle');
    toggles.forEach(function (toggle) { toggle.setAttribute('aria-expanded', 'false'); });
  };

  var setMobileMenuOpen = function (open) {
    if (!nav) return;
    var shouldOpen = !!open && mobileQuery.matches;
    nav.classList.toggle('open', shouldOpen);
    document.body.classList.toggle('btx-mobile-menu-open', shouldOpen);
    if (navToggleButton) navToggleButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    if (!shouldOpen) closeAllMobileSubmenus();
    recalcLayoutVars();
    syncHeaderFill();
  };

  var buildMobileSubmenuToggles = function () {
    if (!nav) return;
    var parents = nav.querySelectorAll('li.menu-item-has-children');
    parents.forEach(function (parent) {
      var submenu = getDirectChildByClass(parent, 'sub-menu');
      var link = getDirectLink(parent);
      if (!submenu || !link) return;
      if (getDirectChildByClass(parent, 'btx-submenu-toggle')) return;

      if (!submenu.id) {
        submenuIdSeed += 1;
        submenu.id = 'btx-submenu-' + submenuIdSeed;
      }

      var toggle = document.createElement('button');
      toggle.type = 'button';
      toggle.className = 'btx-submenu-toggle';
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-controls', submenu.id);
      toggle.setAttribute('aria-label', 'Toggle submenu');
      link.insertAdjacentElement('afterend', toggle);

      toggle.addEventListener('click', function (event) {
        if (!mobileQuery.matches) return;
        event.preventDefault();
        event.stopPropagation();

        var willOpen = !parent.classList.contains('is-submenu-open');
        if (parent.parentElement) {
          Array.prototype.forEach.call(parent.parentElement.children, function (sibling) {
            if (sibling === parent) return;
            sibling.classList.remove('is-submenu-open');
            var siblingToggle = getDirectChildByClass(sibling, 'btx-submenu-toggle');
            if (siblingToggle) siblingToggle.setAttribute('aria-expanded', 'false');
          });
        }
        parent.classList.toggle('is-submenu-open', willOpen);
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });
    });
  };

  recalcLayoutVars();
  syncHeaderFill();
  window.addEventListener('scroll', queueHeaderSync, { passive: true });
  window.addEventListener('resize', function () {
    recalcLayoutVars();
    syncHeaderFill();
  });
  window.addEventListener('load', function () {
    recalcLayoutVars();
    syncHeaderFill();
  });

  var headerBar = document.querySelector('.btx-header-bar');
  var injectedWrap = document.getElementById('starscream-injected-nav');
  var injectedNav  = injectedWrap ? injectedWrap.querySelector('.site-nav') : null;

  // If header exists and doesn't already contain a .site-nav, move injected nav into it.
  if (headerBar && injectedNav && !headerBar.querySelector('.site-nav')) {
    var icons = headerBar.querySelector('.header-icons');
    headerBar.insertBefore(injectedNav, icons || null);
  }

  // Determine the nav we will control (prefer header one, fallback to injected).
  nav = (headerBar && headerBar.querySelector('.site-nav')) ? headerBar.querySelector('.site-nav') : injectedNav;
  if (!nav) return;

  // No menu items? Skip building a toggle so the mobile hamburger stays hidden.
  if (!nav.querySelector('li')) return;

  // Ensure it has an id for aria-controls.
  if (!nav.id) nav.id = 'primary-menu';
  buildMobileSubmenuToggles();
  buildMobileMenuMeta();

  // If header is present, create a hamburger before nav; otherwise leave nav as-is.
  if (headerBar) {
    headerBar.classList.add('has-nav-toggle');
    navToggleButton = headerBar.querySelector('.btx-nav-toggle');
    if (!navToggleButton) {
      navToggleButton = document.createElement('button');
      navToggleButton.type = 'button';
      navToggleButton.className = 'btx-nav-toggle';
      navToggleButton.setAttribute('aria-controls', nav.id);
      navToggleButton.setAttribute('aria-expanded', 'false');
      navToggleButton.setAttribute('aria-label', 'Toggle navigation');
      navToggleButton.innerHTML =
        '<span class="btx-nav-bar"></span>' +
        '<span class="btx-nav-bar"></span>' +
        '<span class="btx-nav-bar"></span>' +
        '<span class="screen-reader-text">Toggle navigation</span>';
      headerBar.insertBefore(navToggleButton, nav);
    }

    navToggleButton.addEventListener('click', function (event) {
      event.preventDefault();
      setMobileMenuOpen(!nav.classList.contains('open'));
    });
  }

  document.addEventListener('keyup', function (event) {
    var key = event.key || event.code;
    if (key === 'Escape' || key === 'Esc') setMobileMenuOpen(false);
  });

  document.addEventListener('click', function (event) {
    if (!mobileQuery.matches) return;
    if (!nav.classList.contains('open')) return;
    if (siteHeader && siteHeader.contains(event.target)) return;
    setMobileMenuOpen(false);
  });

  window.addEventListener('resize', function () {
    if (!mobileQuery.matches) setMobileMenuOpen(false);
    buildMobileMenuMeta();
  });

  recalcLayoutVars();
  syncHeaderFill();
});
