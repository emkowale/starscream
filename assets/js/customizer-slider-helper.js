(function ($) {
  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function settingId(slide, variant) {
    return 'header_slider_slide_' + slide + '_' + variant + '_image_id';
  }

  function bindSetting(id, callback) {
    if (!window.wp || !wp.customize) return;
    wp.customize(id, function (setting) {
      callback(setting.get());
      setting.bind(callback);
    });
  }

  function getCurrentSlide() {
    if (!window.wp || !wp.customize) return 1;
    var value = 1;
    wp.customize('header_slider_editor_slide', function (setting) {
      value = parseInt(setting.get() || '1', 10) || 1;
    });
    return value;
  }

  function fetchAttachment(id) {
    return new Promise(function (resolve) {
      var attachment;
      var done;

      if (!id || !window.wp || !wp.media || !wp.media.attachment) {
        resolve(null);
        return;
      }

      attachment = wp.media.attachment(id);
      done = function () {
        var json = attachment && attachment.toJSON ? attachment.toJSON() : null;
        resolve(json && json.url ? json : null);
      };

      if (attachment.get && attachment.get('url')) {
        done();
        return;
      }

      attachment.fetch({
        success: done,
        error: function () {
          resolve(null);
        }
      });
    });
  }

  function previewUrl(attachment) {
    if (!attachment) return '';
    if (attachment.sizes && attachment.sizes.large && attachment.sizes.large.url) return attachment.sizes.large.url;
    if (attachment.sizes && attachment.sizes.medium_large && attachment.sizes.medium_large.url) return attachment.sizes.medium_large.url;
    if (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) return attachment.sizes.medium.url;
    return attachment.url || '';
  }

  function attachmentMetaText(attachment, fallback) {
    if (!attachment) return fallback;

    var label = attachment.filename || attachment.title || ('Attachment #' + attachment.id);
    var width = attachment.width || 0;
    var height = attachment.height || 0;
    var size = width && height ? width + ' x ' + height : '';

    return size ? label + ' | ' + size : label;
  }

  function renderAttachment($root, type, attachment) {
    var $img = $root.find('[data-slider-helper-image="' + type + '"]');
    var $empty = $root.find('[data-slider-helper-empty="' + type + '"]');
    var $meta = $root.find('[data-slider-helper-meta="' + type + '"]');
    var src = previewUrl(attachment);
    var fallback = type === 'desktop'
      ? 'No desktop image selected.'
      : 'No mobile image selected.';

    if (!attachment || !src) {
      $img.prop('hidden', true).attr('src', '');
      $empty.prop('hidden', false);
      $meta.text(fallback);
      return;
    }

    $img.attr('src', src).attr('alt', attachment.title || '').prop('hidden', false);
    $empty.prop('hidden', true);
    $meta.text(attachmentMetaText(attachment, fallback));
  }

  function setStatus($root, type, message) {
    var className = 'notice-info';
    if (type === 'success') className = 'notice-success';
    if (type === 'error') className = 'notice-error';

    $root.find('.starscream-slider-helper__status').html(
      '<div class="notice inline ' + className + '"><p>' + escapeHtml(message) + '</p></div>'
    );
  }

  function setBusy($root, isBusy) {
    $root.toggleClass('is-busy', !!isBusy);
    $root.find('.starscream-slider-helper__generate-mobile').prop('disabled', !!isBusy);
  }

  function updateButtonState($root, desktopId, hasMobile) {
    var $button = $root.find('.starscream-slider-helper__generate-mobile');
    if (!desktopId) {
      $button.prop('disabled', true).text('Generate mobile crop from desktop');
      return;
    }

    if ($root.hasClass('is-busy')) return;

    $button.prop('disabled', false).text(hasMobile ? 'Regenerate mobile crop from desktop' : 'Generate mobile crop from desktop');
  }

  function refreshHelper($root) {
    var slide = getCurrentSlide();
    var desktopId = 0;
    var mobileId = 0;

    $root.find('[data-slider-helper-slide-number]').text(String(slide));

    wp.customize(settingId(slide, 'desktop'), function (setting) {
      desktopId = parseInt(setting.get() || '0', 10) || 0;
    });

    wp.customize(settingId(slide, 'mobile'), function (setting) {
      mobileId = parseInt(setting.get() || '0', 10) || 0;
    });

    Promise.all([fetchAttachment(desktopId), fetchAttachment(mobileId)]).then(function (results) {
      renderAttachment($root, 'desktop', results[0]);
      renderAttachment($root, 'mobile', results[1]);
      updateButtonState($root, desktopId, !!results[1]);
    });
  }

  $(function () {
    var config = window.starscreamSliderHelper || {};

    $('.starscream-slider-helper').each(function () {
      var $root = $(this);
      var maxSlides = parseInt($root.attr('data-max-slides') || config.maxSlides || '6', 10) || 6;

      bindSetting('header_slider_editor_slide', function () {
        refreshHelper($root);
      });

      for (var i = 1; i <= maxSlides; i += 1) {
        bindSetting(settingId(i, 'desktop'), function () {
          refreshHelper($root);
        });
        bindSetting(settingId(i, 'mobile'), function () {
          refreshHelper($root);
        });
      }

      refreshHelper($root);

      $root.on('click', '.starscream-slider-helper__generate-mobile', function (event) {
        var slide = getCurrentSlide();
        var desktopValue = 0;
        var mobileSetting = settingId(slide, 'mobile');
        var payload;

        event.preventDefault();

        wp.customize(settingId(slide, 'desktop'), function (setting) {
          desktopValue = parseInt(setting.get() || '0', 10) || 0;
        });

        if (!desktopValue) {
          setStatus($root, 'error', config.messages && config.messages.missingDesktop ? config.messages.missingDesktop : 'Choose a desktop image before generating the mobile crop.');
          updateButtonState($root, 0, false);
          return;
        }

        setBusy($root, true);
        setStatus($root, 'info', config.messages && config.messages.generating ? config.messages.generating : 'Generating the mobile crop from the desktop image.');

        payload = new URLSearchParams();
        payload.set('action', 'starscream_generate_slider_mobile_crop');
        payload.set('nonce', config.nonce || '');
        payload.set('attachment_id', String(desktopValue));

        fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: payload.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          if (!json || !json.success || !json.data || !json.data.attachment_id) {
            var message = json && json.data && json.data.message ? json.data.message : (config.messages && config.messages.failed ? config.messages.failed : 'The mobile crop could not be created.');
            setStatus($root, 'error', message);
            return;
          }

          wp.customize(mobileSetting, function (setting) {
            setting.set(parseInt(json.data.attachment_id, 10) || 0);
          });

          setStatus($root, 'success', config.messages && config.messages.generated ? config.messages.generated : 'Generated a new mobile crop and assigned it to the current slide.');
        }).catch(function () {
          setStatus($root, 'error', config.messages && config.messages.failed ? config.messages.failed : 'The mobile crop could not be created.');
        }).finally(function () {
          setBusy($root, false);
          refreshHelper($root);
        });
      });
    });
  });
})(jQuery);
