(function(blocks, element, i18n, components, blockEditor) {
  'use strict';

  var el = element.createElement;
  var registerBlockType = blocks.registerBlockType;
  var __ = i18n.__;

  // Register userspn/profile block
  registerBlockType('userspn/profile', {
    apiVersion: 3,
    title: __('User Profile', 'userspn'),
    description: __('Display user profile or login/registration forms.', 'userspn'),
    icon: 'admin-users',
    category: 'widgets',
    attributes: {},
    edit: function(props) {
      var previewContent = typeof userspnBlocks !== 'undefined' && userspnBlocks.previewContent ? userspnBlocks.previewContent : '';

      return el(
        'div',
        { className: 'userspn-profile-block' },
        el('div', { 
          className: 'userspn-profile-block-preview',
          dangerouslySetInnerHTML: { __html: previewContent }
        })
      );
    },
    save: function() {
      // Server-side rendered dynamic block
      return null;
    },
  });

  // Register userspn/sequential-login block
  registerBlockType('userspn/sequential-login', {
    apiVersion: 3,
    title: __('Sequential Login', 'userspn'),
    description: __('Two-step sequential login form with stacked email and password fields.', 'userspn'),
    icon: 'lock',
    category: 'widgets',
    attributes: {
      redirectPageId: {
        type: 'string',
        default: '',
      },
      redirectTo: {
        type: 'string',
        default: '',
      },
      buttonText: {
        type: 'string',
        default: '',
      },
      loginButtonText: {
        type: 'string',
        default: '',
      },
      showRemember: {
        type: 'boolean',
        default: true,
      },
      showLostPassword: {
        type: 'boolean',
        default: true,
      },
    },
    edit: function(props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;

      var InspectorControls = blockEditor && blockEditor.InspectorControls ? blockEditor.InspectorControls : null;
      var PanelBody = components && components.PanelBody ? components.PanelBody : null;
      var SelectControl = components && components.SelectControl ? components.SelectControl : null;
      var TextControl = components && components.TextControl ? components.TextControl : null;
      var ToggleControl = components && components.ToggleControl ? components.ToggleControl : null;

      var pagesOptions = (typeof userspnBlocks !== 'undefined' && userspnBlocks.pages) ? userspnBlocks.pages : [
        { value: '', label: __('— Select destination page —', 'userspn') }
      ];

      var selectedPageLabel = '';
      if (attributes.redirectPageId) {
        for (var i = 0; i < pagesOptions.length; i++) {
          if (pagesOptions[i].value === attributes.redirectPageId) {
            selectedPageLabel = pagesOptions[i].label;
            break;
          }
        }
      }

      var controls = null;
      if (InspectorControls && PanelBody && TextControl && ToggleControl) {
        controls = el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Sequential Login Settings', 'userspn'), initialOpen: true },
            SelectControl ? el(SelectControl, {
              label: __('Destination Page (after login)', 'userspn'),
              value: attributes.redirectPageId || '',
              options: pagesOptions,
              onChange: function(val) { setAttributes({ redirectPageId: val }); },
              help: __('Choose the page to redirect users to after successful login. If an already logged-in user visits this form, they will be automatically redirected to this page.', 'userspn'),
            }) : null,
            el(TextControl, {
              label: __('Custom Redirect URL (optional fallback)', 'userspn'),
              value: attributes.redirectTo || '',
              placeholder: 'https://...',
              onChange: function(val) { setAttributes({ redirectTo: val }); },
              help: __('Optional custom URL if not selecting a page above.', 'userspn'),
            }),
            el(TextControl, {
              label: __('Step 1 Button Text', 'userspn'),
              value: attributes.buttonText || '',
              placeholder: __('Continue', 'userspn'),
              onChange: function(val) { setAttributes({ buttonText: val }); },
            }),
            el(TextControl, {
              label: __('Step 2 Button Text', 'userspn'),
              value: attributes.loginButtonText || '',
              placeholder: __('Log In', 'userspn'),
              onChange: function(val) { setAttributes({ loginButtonText: val }); },
            }),
            el(ToggleControl, {
              label: __('Show Remember Me checkbox', 'userspn'),
              checked: attributes.showRemember !== false,
              onChange: function(val) { setAttributes({ showRemember: val }); },
            }),
            el(ToggleControl, {
              label: __('Show Forgot Password link', 'userspn'),
              checked: attributes.showLostPassword !== false,
              onChange: function(val) { setAttributes({ showLostPassword: val }); },
            })
          )
        );
      }

      var previewHtml = typeof userspnBlocks !== 'undefined' && userspnBlocks.sequentialLoginPreviewContent ? userspnBlocks.sequentialLoginPreviewContent : '';

      return el(
        'div',
        { className: 'userspn-sequential-login-block-wrapper' },
        controls,
        el(
          'div',
          {
            className: 'userspn-sequential-login-editor-preview',
            style: {
              maxWidth: '420px',
              margin: '20px auto',
              padding: '24px',
              border: '1px solid #e2e8f0',
              borderRadius: '8px',
              backgroundColor: '#ffffff',
              boxShadow: '0 4px 15px rgba(0,0,0,0.05)',
              fontFamily: 'inherit'
            }
          },
          el(
            'div',
            { style: { textAlign: 'center', marginBottom: '16px' } },
            el('h4', { style: { margin: '0 0 4px 0', fontSize: '18px', fontWeight: 'bold' } }, __('Sequential Login (Preview)', 'userspn')),
            el('p', { style: { margin: 0, fontSize: '12px', color: '#64748b' } }, __('Step 1: Email Address -> Step 2: Password', 'userspn'))
          ),
          el(
            'div',
            { style: { marginBottom: '14px' } },
            el('label', { style: { display: 'block', fontSize: '13px', fontWeight: '600', marginBottom: '6px', color: '#334155' } }, __('Email address or username', 'userspn')),
            el('input', {
              type: 'text',
              disabled: true,
              placeholder: __('Email address or username', 'userspn'),
              style: {
                width: '100%',
                padding: '10px 14px',
                border: '1px solid #cbd5e1',
                borderRadius: '6px',
                boxSizing: 'border-box',
                backgroundColor: '#f8fafc',
                fontSize: '14px'
              }
            })
          ),
          el(
            'div',
            { style: { marginBottom: '10px' } },
            el('button', {
              type: 'button',
              disabled: true,
              style: {
                width: '100%',
                padding: '12px 20px',
                backgroundColor: '#00aa44',
                color: '#ffffff',
                border: 'none',
                borderRadius: '6px',
                fontWeight: '600',
                fontSize: '14px',
                cursor: 'default'
              }
            }, attributes.buttonText || __('Continue', 'userspn'))
          ),
          el(
            'div',
            { style: { textAlign: 'center', marginTop: '12px', fontSize: '11px', color: '#94a3b8' } },
            __('Flow proceeds to password entry and authentication on submission.', 'userspn')
          ),
          selectedPageLabel ? el(
            'div',
            { style: { marginTop: '10px', padding: '6px 10px', backgroundColor: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: '4px', fontSize: '11px', color: '#166534', textAlign: 'center' } },
            __('Destination Page:', 'userspn') + ' ' + selectedPageLabel
          ) : null
        )
      );
    },
    save: function() {
      // Server-side rendered dynamic block
      return null;
    },
  });

})(
  window.wp.blocks,
  window.wp.element,
  window.wp.i18n,
  window.wp.components,
  window.wp.blockEditor || window.wp.editor
);
