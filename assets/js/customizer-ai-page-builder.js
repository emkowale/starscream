(function ($) {
  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function getGuideHostWindow() {
    try {
      if (window.top && window.top.document && window.top.document.body) {
        return window.top;
      }
    } catch (error) {}

    return window;
  }

  function ensureGuideFrameStyles(hostDocument) {
    var styleId = 'starscream-ai-page-builder-guide-frame-styles';
    var style = hostDocument.getElementById(styleId);

    if (style) {
      return;
    }

    style = hostDocument.createElement('style');
    style.id = styleId;
    style.textContent = [
      'body.starscream-ai-guide-frame-open{overflow:hidden;}',
      '.starscream-ai-guide-frame{position:fixed;inset:20px;z-index:500000;display:none;align-items:center;justify-content:center;}',
      '.starscream-ai-guide-frame.is-open{display:flex;}',
      '.starscream-ai-guide-frame::before{content:\"\";position:absolute;inset:0;border-radius:20px;background:rgba(15,23,42,.54);backdrop-filter:blur(3px);}',
      '.starscream-ai-guide-frame__dialog{position:relative;display:grid;grid-template-rows:auto minmax(0,1fr);width:min(1320px,calc(100vw - 48px));height:min(920px,calc(100vh - 48px));border-radius:20px;overflow:hidden;background:#fff;box-shadow:0 32px 80px -32px rgba(15,23,42,.65);}',
      '.starscream-ai-guide-frame__bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 18px;background:linear-gradient(135deg,#072b3d 0%,#0f6d8c 100%);color:#f8fafc;}',
      '.starscream-ai-guide-frame__bar strong{font-size:14px;line-height:1.3;}',
      '.starscream-ai-guide-frame__close.button{margin:0;flex:0 0 auto;}',
      '.starscream-ai-guide-frame__iframe{display:block;width:100%;height:100%;border:0;background:#eef4f8;}',
      '@media (max-width: 900px){.starscream-ai-guide-frame{inset:12px;}.starscream-ai-guide-frame__dialog{width:calc(100vw - 24px);height:calc(100vh - 24px);}.starscream-ai-guide-frame__bar{padding:12px 14px;}}'
    ].join('');

    hostDocument.head.appendChild(style);
  }

  function closeGuideFrame(hostWindow) {
    var overlay;
    var hostDocument;

    hostWindow = hostWindow || getGuideHostWindow();
    hostDocument = hostWindow.document;
    overlay = hostDocument.getElementById('starscream-ai-page-builder-guide-frame');

    if (!overlay) {
      return;
    }

    overlay.hidden = true;
    overlay.classList.remove('is-open');
    hostDocument.body.classList.remove('starscream-ai-guide-frame-open');
  }

  function ensureGuideFrame(hostWindow) {
    var hostDocument = hostWindow.document;
    var overlay = hostDocument.getElementById('starscream-ai-page-builder-guide-frame');

    ensureGuideFrameStyles(hostDocument);

    if (overlay) {
      return overlay;
    }

    overlay = hostDocument.createElement('div');
    overlay.id = 'starscream-ai-page-builder-guide-frame';
    overlay.className = 'starscream-ai-guide-frame';
    overlay.hidden = true;
    overlay.innerHTML = [
      '<div class="starscream-ai-guide-frame__dialog" role="dialog" aria-modal="true" aria-label="Page Builder Guide">',
      '<div class="starscream-ai-guide-frame__bar">',
      '<strong>Starscream Page Builder Guide</strong>',
      '<button type="button" class="button button-secondary starscream-ai-guide-frame__close" data-starscream-guide-close>Close guide</button>',
      '</div>',
      '<iframe class="starscream-ai-guide-frame__iframe" title="Starscream Page Builder Guide" loading="lazy"></iframe>',
      '</div>'
    ].join('');

    overlay.addEventListener('click', function (event) {
      if (event.target === overlay || event.target.closest('[data-starscream-guide-close]')) {
        closeGuideFrame(hostWindow);
      }
    });

    hostDocument.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeGuideFrame(hostWindow);
      }
    });

    hostDocument.body.appendChild(overlay);
    return overlay;
  }

  function openGuideFrame(url) {
    var hostWindow;
    var hostDocument;
    var overlay;
    var iframe;
    var closeButton;

    if (!url) {
      return false;
    }

    hostWindow = getGuideHostWindow();
    hostDocument = hostWindow.document;
    overlay = ensureGuideFrame(hostWindow);
    iframe = overlay.querySelector('.starscream-ai-guide-frame__iframe');
    closeButton = overlay.querySelector('[data-starscream-guide-close]');

    if (!iframe) {
      return false;
    }

    if (iframe.getAttribute('src') !== url) {
      iframe.setAttribute('src', url);
    }

    overlay.hidden = false;
    overlay.classList.add('is-open');
    hostDocument.body.classList.add('starscream-ai-guide-frame-open');

    if (closeButton) {
      closeButton.focus();
    }

    return true;
  }

  function renderImages($root, attachments) {
    var $input = $root.find('.starscream-ai-page-builder__image-ids');
    var $list = $root.find('.starscream-ai-page-builder__image-list');
    var $clear = $root.find('.starscream-ai-page-builder__clear-images');

    if (!attachments.length) {
      $input.val('');
      $list.empty().prop('hidden', true);
      $clear.prop('hidden', true);
      return;
    }

    $input.val(attachments.map(function (attachment) { return attachment.id; }).join(','));
    $list.empty().prop('hidden', false);
    $clear.prop('hidden', false);

    attachments.forEach(function (attachment) {
      var thumb = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      var label = attachment.alt || attachment.title || ('Image #' + attachment.id);
      var card = [
        '<div class="starscream-ai-page-builder__image-card">',
        '<img src="' + String(thumb || '').replace(/"/g, '&quot;') + '" alt="">',
        '<span>' + escapeHtml(label) + '</span>',
        '</div>'
      ].join('');
      $list.append(card);
    });
  }

  function toggleMode($root) {
    var mode = $root.find('input[type="radio"]:checked').val();
    var isExisting = mode === 'existing';
    $root.find('.starscream-ai-page-builder__field--existing').prop('hidden', !isExisting);
    $root.find('.starscream-ai-page-builder__field--new').prop('hidden', isExisting);
    $root.find('.starscream-ai-page-builder__warning').prop('hidden', !isExisting);
  }

  function setBusy($root, isBusy) {
    var $modal = $root.find('.starscream-ai-page-builder__modal');
    $modal.prop('hidden', !isBusy);
    $root.find('.starscream-ai-page-builder__build').prop('disabled', isBusy);
    $root.find('.starscream-ai-page-builder__suggest-replacements').prop('disabled', isBusy);
    $root.find('.starscream-ai-page-builder__apply-replacements').prop('disabled', isBusy || !$root.find('.starscream-ai-page-builder__replacements-json').val());
  }

  function updateBusyTitle($root, title) {
    $root.find('.starscream-ai-page-builder__modal-card strong').text(title);
  }

  function updateStatus($root, message) {
    $root.find('.starscream-ai-page-builder__modal-message').text(message);
  }

  function readCustomizerInput(controlId) {
    var $control = $('#' + controlId);
    if (!$control.length) return '';
    var $input = $control.find('input');
    return $.trim($input.val() || '');
  }

  function showNotice($container, type, html) {
    var noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
    $container.html('<div class="notice ' + noticeClass + ' inline">' + html + '</div>');
  }

  function clearNotice($container) {
    $container.empty();
  }

  function showResult($root, type, html) {
    showNotice($root.find('.starscream-ai-page-builder__result'), type, html);
  }

  function showReplaceResult($root, type, html) {
    showNotice($root.find('.starscream-ai-page-builder__replace-result'), type, html);
  }

  function clearSuggestions($root) {
    $root.find('.starscream-ai-page-builder__replacements-json').val('');
    $root.find('.starscream-ai-page-builder__suggestions').empty().prop('hidden', true);
    $root.find('.starscream-ai-page-builder__apply-replacements').prop('hidden', true).prop('disabled', true);
  }

  function renderSuggestionCards($root, suggestions) {
    var $suggestions = $root.find('.starscream-ai-page-builder__suggestions');
    var $apply = $root.find('.starscream-ai-page-builder__apply-replacements');

    if (!suggestions.length) {
      clearSuggestions($root);
      return;
    }

    $root.find('.starscream-ai-page-builder__replacements-json').val(JSON.stringify(suggestions));
    $suggestions.empty().prop('hidden', false);
    $apply.prop('hidden', false).prop('disabled', false);

    suggestions.forEach(function (suggestion) {
      var current = suggestion.current || {};
      var replacement = suggestion.replacement || {};
      var currentLabel = current.label || current.context_heading || 'Current image';
      var currentAlt = current.alt || 'No alt text on current image';
      var replacementLabel = replacement.alt || replacement.title || ('Attachment #' + replacement.attachment_id);
      var reason = suggestion.reason || 'Selected because it fits this section better.';
      var context = current.context_text || current.context_heading || '';
      var currentBadge = current.is_external
        ? '<span class="starscream-ai-page-builder__badge starscream-ai-page-builder__badge--external">External</span>'
        : '<span class="starscream-ai-page-builder__badge">Current</span>';
      var replacementBadge = '<span class="starscream-ai-page-builder__badge starscream-ai-page-builder__badge--library">Media library</span>';

      var card = [
        '<article class="starscream-ai-page-builder__suggestion-card">',
        '<div class="starscream-ai-page-builder__suggestion-head">',
        '<strong>' + escapeHtml(currentLabel) + '</strong>',
        '</div>',
        '<div class="starscream-ai-page-builder__suggestion-pair">',
        '<div class="starscream-ai-page-builder__suggestion-media">',
        currentBadge,
        '<img src="' + escapeHtml(current.url || '') + '" alt="">',
        '<div class="starscream-ai-page-builder__suggestion-meta">',
        '<strong>Current image</strong>',
        '<span>' + escapeHtml(currentAlt) + '</span>',
        '</div>',
        '</div>',
        '<div class="starscream-ai-page-builder__suggestion-media">',
        replacementBadge,
        '<img src="' + escapeHtml(replacement.thumb_url || replacement.url || '') + '" alt="">',
        '<div class="starscream-ai-page-builder__suggestion-meta">',
        '<strong>Replace with #' + escapeHtml(replacement.attachment_id) + '</strong>',
        '<span>' + escapeHtml(replacementLabel) + '</span>',
        '</div>',
        '</div>',
        '</div>',
        '<p class="starscream-ai-page-builder__suggestion-reason">' + escapeHtml(reason) + '</p>',
        context ? '<p class="starscream-ai-page-builder__suggestion-context">' + escapeHtml(context) + '</p>' : '',
        '</article>'
      ].join('');

      $suggestions.append(card);
    });
  }

  function showBuilderSuccess($root, data) {
    var links = [];
    if (data.edit_url) {
      links.push('<a href="' + escapeHtml(data.edit_url) + '" target="_blank" rel="noopener">Edit page</a>');
    }
    if (data.view_url) {
      links.push('<a href="' + escapeHtml(data.view_url) + '" target="_blank" rel="noopener">View page</a>');
    }

    var sourceHtml = '';
    if (Array.isArray(data.source_urls) && data.source_urls.length) {
      sourceHtml = '<ul class="starscream-ai-page-builder__sources">' + data.source_urls.map(function (url) {
        var safeUrl = escapeHtml(url);
        return '<li><a href="' + safeUrl + '" target="_blank" rel="noopener">' + safeUrl + '</a></li>';
      }).join('') + '</ul>';
    }

    var parts = [];
    parts.push('<p><strong>' + escapeHtml(data.page_title || 'Page built') + '</strong> was ' + (data.mode === 'existing' ? 'updated.' : 'created as a draft.') + '</p>');
    if (data.summary) {
      parts.push('<p>' + escapeHtml(data.summary) + '</p>');
    }
    if (links.length) {
      parts.push('<div class="starscream-ai-page-builder__result-links">' + links.join('') + '</div>');
    }
    if (sourceHtml) {
      parts.push('<p><strong>Sources used</strong></p>' + sourceHtml);
    }

    showResult($root, 'success', parts.join(''));
  }

  function showReplaceSuccess($root, data) {
    var links = [];
    if (data.edit_url) {
      links.push('<a href="' + escapeHtml(data.edit_url) + '" target="_blank" rel="noopener">Edit page</a>');
    }
    if (data.view_url) {
      links.push('<a href="' + escapeHtml(data.view_url) + '" target="_blank" rel="noopener">View page</a>');
    }

    var parts = [
      '<p><strong>' + escapeHtml(data.page_title || 'Page updated') + '</strong> was updated.</p>',
      '<p>Replaced ' + escapeHtml(data.updated_count || 0) + ' image slot' + (Number(data.updated_count || 0) === 1 ? '' : 's') + ' with media library images.</p>'
    ];

    if (links.length) {
      parts.push('<div class="starscream-ai-page-builder__result-links">' + links.join('') + '</div>');
    }

    showReplaceResult($root, 'success', parts.join(''));
  }

  function startBusy($root, title, messages) {
    var safeMessages = Array.isArray(messages) && messages.length ? messages : ['Working...'];
    var index = 0;

    updateBusyTitle($root, title);
    updateStatus($root, safeMessages[0]);
    setBusy($root, true);

    return window.setInterval(function () {
      index = (index + 1) % safeMessages.length;
      updateStatus($root, safeMessages[index]);
    }, 2800);
  }

  function stopBusy($root, intervalId) {
    if (intervalId) {
      window.clearInterval(intervalId);
    }
    updateBusyTitle($root, 'AI page builder is working...');
    updateStatus($root, 'Researching the client and drafting the page.');
    setBusy($root, false);
  }

  $(function () {
    $('.starscream-ai-page-builder').each(function () {
      var $root = $(this);
      var mediaFrame = null;
      var config = window.starscreamAiPageBuilder || {};
      var messages = config.messages || {};

      toggleMode($root);

      $root.on('change', 'input[type="radio"]', function () {
        toggleMode($root);
      });

      $root.on('change', '.starscream-ai-page-builder__replace-page', function () {
        clearSuggestions($root);
        clearNotice($root.find('.starscream-ai-page-builder__replace-result'));
      });

      $root.on('click', '.starscream-ai-page-builder__select-images', function (event) {
        event.preventDefault();

        if (!mediaFrame) {
          mediaFrame = wp.media({
            title: 'Choose client images',
            library: { type: 'image' },
            button: { text: 'Use selected images' },
            multiple: true
          });

          mediaFrame.on('select', function () {
            var attachments = mediaFrame.state().get('selection').map(function (attachment) {
              return attachment.toJSON();
            });
            renderImages($root, attachments);
          });
        }

        mediaFrame.open();
      });

      $root.on('click', '.starscream-ai-page-builder__clear-images', function (event) {
        event.preventDefault();
        renderImages($root, []);
      });

      $root.on('click', '.starscream-ai-page-builder__open-guide', function (event) {
        var url = $(this).attr('href') || config.guideUrl || '';

        event.preventDefault();

        if (!url) {
          return;
        }

        if (!openGuideFrame(url)) {
          window.open(url, '_blank', 'noopener');
        }
      });

      $root.on('click', '.starscream-ai-page-builder__build', function (event) {
        var mode;
        var pageId;
        var newTitle;
        var brief;
        var imageIds;
        var apiKey;
        var model;
        var intervalId;
        var payload;

        event.preventDefault();

        mode = $root.find('input[type="radio"]:checked').val();
        pageId = $.trim($root.find('.starscream-ai-page-builder__existing-page').val() || '');
        newTitle = $.trim($root.find('.starscream-ai-page-builder__new-title').val() || '');
        brief = $.trim($root.find('.starscream-ai-page-builder__brief').val() || '');
        imageIds = $.trim($root.find('.starscream-ai-page-builder__image-ids').val() || '');
        apiKey = readCustomizerInput(config.apiKeyControlId || '');
        model = readCustomizerInput(config.modelControlId || '');

        if (mode === 'existing' && !pageId) {
          showResult($root, 'error', '<p>' + escapeHtml(messages.missingExistingPage) + '</p>');
          return;
        }

        if (mode === 'new' && !newTitle) {
          showResult($root, 'error', '<p>' + escapeHtml(messages.missingNewTitle) + '</p>');
          return;
        }

        if (mode === 'existing' && !window.confirm(messages.overwriteConfirm)) {
          return;
        }

        intervalId = startBusy($root, 'AI page builder is working...', messages.building);

        payload = new URLSearchParams();
        payload.set('action', 'starscream_ai_page_builder_generate');
        payload.set('nonce', config.nonce);
        payload.set('mode', mode);
        payload.set('page_id', pageId);
        payload.set('new_title', newTitle);
        payload.set('brief', brief);
        payload.set('image_ids', imageIds);
        payload.set('api_key_override', apiKey);
        payload.set('model_override', model);

        fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: payload.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          if (!json || !json.success) {
            var message = json && json.data && json.data.message ? json.data.message : 'The AI builder failed.';
            showResult($root, 'error', '<p>' + escapeHtml(message) + '</p>');
            return;
          }

          showBuilderSuccess($root, json.data || {});
        }).catch(function () {
          showResult($root, 'error', '<p>The request failed before the AI builder could finish.</p>');
        }).finally(function () {
          stopBusy($root, intervalId);
        });
      });

      $root.on('click', '.starscream-ai-page-builder__suggest-replacements', function (event) {
        var pageId;
        var apiKey;
        var model;
        var intervalId;
        var payload;

        event.preventDefault();

        pageId = $.trim($root.find('.starscream-ai-page-builder__replace-page').val() || '');
        apiKey = readCustomizerInput(config.apiKeyControlId || '');
        model = readCustomizerInput(config.modelControlId || '');

        if (!pageId) {
          showReplaceResult($root, 'error', '<p>' + escapeHtml(messages.missingReplacePage) + '</p>');
          return;
        }

        clearSuggestions($root);
        intervalId = startBusy($root, 'AI image replacer is working...', messages.suggesting);

        payload = new URLSearchParams();
        payload.set('action', 'starscream_ai_page_builder_suggest_replacements');
        payload.set('nonce', config.nonce);
        payload.set('page_id', pageId);
        payload.set('api_key_override', apiKey);
        payload.set('model_override', model);

        fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: payload.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          var parts;
          var data;
          var links = [];

          if (!json || !json.success) {
            var message = json && json.data && json.data.message ? json.data.message : 'The AI image replacer failed.';
            showReplaceResult($root, 'error', '<p>' + escapeHtml(message) + '</p>');
            return;
          }

          data = json.data || {};
          renderSuggestionCards($root, Array.isArray(data.suggestions) ? data.suggestions : []);

          if (data.edit_url) {
            links.push('<a href="' + escapeHtml(data.edit_url) + '" target="_blank" rel="noopener">Edit page</a>');
          }
          if (data.view_url) {
            links.push('<a href="' + escapeHtml(data.view_url) + '" target="_blank" rel="noopener">View page</a>');
          }

          parts = [
            '<p><strong>' + escapeHtml(data.page_title || 'Page') + '</strong> has AI-selected replacement suggestions ready to review.</p>'
          ];

          if (data.summary) {
            parts.push('<p>' + escapeHtml(data.summary) + '</p>');
          }
          if (links.length) {
            parts.push('<div class="starscream-ai-page-builder__result-links">' + links.join('') + '</div>');
          }

          showReplaceResult($root, 'success', parts.join(''));
        }).catch(function () {
          showReplaceResult($root, 'error', '<p>The request failed before image suggestions could finish.</p>');
        }).finally(function () {
          stopBusy($root, intervalId);
        });
      });

      $root.on('click', '.starscream-ai-page-builder__apply-replacements', function (event) {
        var pageId;
        var replacementsJson;
        var intervalId;
        var payload;

        event.preventDefault();

        pageId = $.trim($root.find('.starscream-ai-page-builder__replace-page').val() || '');
        replacementsJson = $.trim($root.find('.starscream-ai-page-builder__replacements-json').val() || '');

        if (!pageId) {
          showReplaceResult($root, 'error', '<p>' + escapeHtml(messages.missingReplacePage) + '</p>');
          return;
        }

        if (!replacementsJson) {
          showReplaceResult($root, 'error', '<p>' + escapeHtml(messages.missingReplacementSuggestions) + '</p>');
          return;
        }

        if (!window.confirm(messages.replaceConfirm)) {
          return;
        }

        intervalId = startBusy($root, 'Applying image replacements...', messages.applying);

        payload = new URLSearchParams();
        payload.set('action', 'starscream_ai_page_builder_apply_replacements');
        payload.set('nonce', config.nonce);
        payload.set('page_id', pageId);
        payload.set('replacements_json', replacementsJson);

        fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: payload.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          if (!json || !json.success) {
            var message = json && json.data && json.data.message ? json.data.message : 'Applying image replacements failed.';
            showReplaceResult($root, 'error', '<p>' + escapeHtml(message) + '</p>');
            return;
          }

          clearSuggestions($root);
          showReplaceSuccess($root, json.data || {});
        }).catch(function () {
          showReplaceResult($root, 'error', '<p>The request failed before the page images could be updated.</p>');
        }).finally(function () {
          stopBusy($root, intervalId);
        });
      });
    });
  });
})(jQuery);
