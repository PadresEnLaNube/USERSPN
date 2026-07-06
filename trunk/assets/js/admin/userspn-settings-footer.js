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

  // ── Recommended plugins ──────────────────────────────────────

  var rpBtn   = document.getElementById('userspn-settings-recommended');
  var rpPopup = document.getElementById('userspn-recommended-plugins');

  if (rpBtn && rpPopup) {
    rpBtn.addEventListener('click', function () {
      if (window.USERSPN_Popups) {
        USERSPN_Popups.open('userspn-recommended-plugins');
      }
    });

    rpPopup.addEventListener('click', function (e) {
      var installBtn  = e.target.closest('.pn-cm-rp-install');
      var activateBtn = e.target.closest('.pn-cm-rp-activate');

      if (installBtn)  handleRpInstall(installBtn);
      if (activateBtn) handleRpActivate(activateBtn);
    });
  }

  function handleRpInstall(btn) {
    var slug      = btn.getAttribute('data-slug');
    var card      = btn.closest('.pn-cm-rp-card');
    var actionDiv = card.querySelector('.pn-cm-rp-action');
    var i18n      = userspnSettingsFooter.i18n;

    btn.disabled    = true;
    btn.textContent = i18n.installing;

    var fd = new FormData();
    fd.append('action', 'userspn_ajax');
    fd.append('userspn_ajax_type', 'userspn_install_plugin');
    fd.append('userspn_ajax_nonce', userspnSettingsFooter.nonce);
    fd.append('slug', slug);

    fetch(userspnSettingsFooter.ajaxUrl, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.error_key) {
          btn.disabled    = false;
          btn.textContent = 'Install';
          if (typeof userspn_get_main_message === 'function') {
            userspn_get_main_message(res.error_content || i18n.installError, 'red');
          }
          return;
        }
        actionDiv.innerHTML = '<button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent pn-cm-rp-activate" data-slug="' + slug + '">' + i18n.activate + '</button>';
        updateRpBadge(-1);
      })
      .catch(function () {
        btn.disabled    = false;
        btn.textContent = 'Install';
        if (typeof userspn_get_main_message === 'function') {
          userspn_get_main_message(i18n.installError, 'red');
        }
      });
  }

  function handleRpActivate(btn) {
    var slug      = btn.getAttribute('data-slug');
    var card      = btn.closest('.pn-cm-rp-card');
    var actionDiv = card.querySelector('.pn-cm-rp-action');
    var i18n      = userspnSettingsFooter.i18n;

    btn.disabled    = true;
    btn.textContent = i18n.activating;

    var fd = new FormData();
    fd.append('action', 'userspn_ajax');
    fd.append('userspn_ajax_type', 'userspn_activate_plugin');
    fd.append('userspn_ajax_nonce', userspnSettingsFooter.nonce);
    fd.append('slug', slug);

    fetch(userspnSettingsFooter.ajaxUrl, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.error_key) {
          btn.disabled    = false;
          btn.textContent = i18n.activate;
          if (typeof userspn_get_main_message === 'function') {
            userspn_get_main_message(res.error_content || i18n.activateError, 'red');
          }
          return;
        }
        actionDiv.innerHTML = '<span class="pn-cm-rp-active-badge">' + i18n.active + '</span>';
        var settingsUrl = (userspnSettingsFooter.settingsPages || {})[slug];
        if (settingsUrl) {
          window.open(settingsUrl, '_blank');
        }
      })
      .catch(function () {
        btn.disabled    = false;
        btn.textContent = i18n.activate;
        if (typeof userspn_get_main_message === 'function') {
          userspn_get_main_message(i18n.activateError, 'red');
        }
      });
  }

  function updateRpBadge(delta) {
    var badge = document.querySelector('.pn-cm-rp-badge');
    if (!badge) return;
    var count = parseInt(badge.textContent, 10) + delta;
    if (count <= 0) {
      badge.remove();
    } else {
      badge.textContent = count;
    }
  }
})();
