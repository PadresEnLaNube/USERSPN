(function () {
  'use strict';
  var saveBtn = document.getElementById('userspn-settings-save');
  var exportBtn = document.getElementById('userspn-settings-export');
  var importBtn = document.getElementById('userspn-settings-import');
  var fileInput = document.getElementById('userspn-settings-import-file');
  if (!saveBtn) return;

  var menuToggle = document.getElementById('wp-admin-bar-menu-toggle');
  var footer = document.getElementById('userspn-settings-footer');
  if (menuToggle && footer) {
    menuToggle.addEventListener('click', function () {
      setTimeout(function () {
        footer.style.display = document.body.classList.contains('wp-responsive-open') ? 'none' : '';
      }, 0);
    });
  }

  saveBtn.addEventListener('click', function () {
    var form = document.getElementById('userspn_form');
    if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
  });

  exportBtn.addEventListener('click', function () {
    var fd = new FormData();
    fd.append('action', 'userspn_ajax');
    fd.append('userspn_ajax_type', 'userspn_settings_export');
    fd.append('userspn_ajax_nonce', userspnSettingsFooter.nonce);
    fetch(userspnSettingsFooter.ajaxUrl, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.error_key) { if (typeof userspn_get_main_message === 'function') userspn_get_main_message(userspnSettingsFooter.i18n.exportError, 'red'); return; }
        var blob = new Blob([JSON.stringify(res.settings, null, 2)], { type: 'application/json' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'userspn-settings-' + new Date().toISOString().slice(0, 10) + '.json';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
      })
      .catch(function () { if (typeof userspn_get_main_message === 'function') userspn_get_main_message(userspnSettingsFooter.i18n.exportError, 'red'); });
  });

  importBtn.addEventListener('click', function () { fileInput.value = ''; fileInput.click(); });

  fileInput.addEventListener('change', function () {
    var file = fileInput.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      var data;
      try { data = JSON.parse(e.target.result); } catch (err) { if (typeof userspn_get_main_message === 'function') userspn_get_main_message(userspnSettingsFooter.i18n.invalidFile, 'red'); return; }
      if (!confirm(userspnSettingsFooter.i18n.confirmImport)) return;
      var fd = new FormData();
      fd.append('action', 'userspn_ajax');
      fd.append('userspn_ajax_type', 'userspn_settings_import');
      fd.append('userspn_ajax_nonce', userspnSettingsFooter.nonce);
      fd.append('settings', JSON.stringify(data));
      fetch(userspnSettingsFooter.ajaxUrl, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error_key) { if (typeof userspn_get_main_message === 'function') userspn_get_main_message(res.error_content || userspnSettingsFooter.i18n.importError, 'red'); return; }
          if (typeof userspn_get_main_message === 'function') userspn_get_main_message(userspnSettingsFooter.i18n.importSuccess, 'green');
          setTimeout(function () { location.reload(); }, 1500);
        })
        .catch(function () { if (typeof userspn_get_main_message === 'function') userspn_get_main_message(userspnSettingsFooter.i18n.importError, 'red'); });
    };
    reader.readAsText(file);
  });
})();
