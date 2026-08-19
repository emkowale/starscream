document.addEventListener('click', function (event) {
  var button = event.target.closest('[data-copy-target]');
  if (!button) return;

  var target = document.getElementById(button.getAttribute('data-copy-target'));
  if (!target) return;

  var originalLabel = button.textContent;
  var text = target.value;

  var onSuccess = function () {
    button.textContent = 'Copied';
    window.setTimeout(function () {
      button.textContent = originalLabel;
    }, 1600);
  };

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(onSuccess, function () {
      target.focus();
      target.select();
      document.execCommand('copy');
      onSuccess();
    });
    return;
  }

  target.focus();
  target.select();
  document.execCommand('copy');
  onSuccess();
});
