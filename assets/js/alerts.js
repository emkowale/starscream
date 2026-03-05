/* Replace native alert() with a Starscream-styled modal */
(function () {
  'use strict';

  if (window.btxStyledAlert) return;

  var nativeAlert = window.alert.bind(window);
  var state = { overlay: null, message: null, ok: null, close: null, dialog: null, lastFocus: null };

  function hideAlert() {
    if (!state.overlay) return;
    state.overlay.classList.remove('is-visible');
    document.body.classList.remove('btx-alert-locked');
    if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
      try { state.lastFocus.focus({ preventScroll: true }); } catch (e) { /* ignore */ }
    }
  }

  function trapKeys(event) {
    if (!state.overlay || !state.overlay.classList.contains('is-visible')) return;

    if (event.key === 'Escape') {
      hideAlert();
    } else if (event.key === 'Tab') {
      var focusables = [state.close, state.ok].filter(Boolean);
      if (!focusables.length) return;
      var first = focusables[0];
      var last = focusables[focusables.length - 1];
      var active = document.activeElement;
      if (event.shiftKey && active === first) {
        event.preventDefault(); last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault(); first.focus();
      }
    }
  }

  function ensureUI() {
    if (state.overlay) return state;
    if (!document.body) return null;

    var overlay = document.createElement('div');
    overlay.className = 'btx-alert-backdrop';
    overlay.innerHTML = ''
      + '<div class="btx-alert" role="alertdialog" aria-modal="true" aria-labelledby="btx-alert-title" aria-describedby="btx-alert-message">'
      + '  <div class="btx-alert__spark" aria-hidden="true"></div>'
      + '  <div class="btx-alert__header">'
      + '    <span class="btx-alert__badge" aria-hidden="true">!</span>'
      + '    <div class="btx-alert__titles">'
      + '      <div class="btx-alert__eyebrow">Heads up</div>'
      + '      <div class="btx-alert__title" id="btx-alert-title">Notice</div>'
      + '    </div>'
      + '    <button type="button" class="btx-alert__close" aria-label="Dismiss alert">&times;</button>'
      + '  </div>'
      + '  <div class="btx-alert__body" id="btx-alert-message"></div>'
      + '  <div class="btx-alert__actions">'
      + '    <button type="button" class="btx-alert__action">Got it</button>'
      + '  </div>'
      + '</div>';

    document.body.appendChild(overlay);

    state.overlay = overlay;
    state.message = overlay.querySelector('#btx-alert-message');
    state.ok = overlay.querySelector('.btx-alert__action');
    state.close = overlay.querySelector('.btx-alert__close');
    state.dialog = overlay.querySelector('.btx-alert');

    var close = function () { hideAlert(); };
    if (state.ok) state.ok.addEventListener('click', close);
    if (state.close) state.close.addEventListener('click', close);
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) hideAlert();
    });
    document.addEventListener('keydown', trapKeys);

    return state;
  }

  function styledAlert(message) {
    if (!document.body) return nativeAlert(message);
    var ui = ensureUI();
    if (!ui) return nativeAlert(message);

    var text = (message === undefined || message === null) ? '' : String(message);
    ui.message.textContent = text;
    ui.dialog.setAttribute('aria-label', text ? 'Alert: ' + text : 'Alert');
    ui.lastFocus = document.activeElement;
    ui.overlay.classList.add('is-visible');
    document.body.classList.add('btx-alert-locked');
    if (ui.ok) ui.ok.focus({ preventScroll: true });
  }

  window.btxStyledAlert = styledAlert;
  window.alert = function (message) {
    try { styledAlert(message); }
    catch (err) { nativeAlert(message); }
  };
})();
