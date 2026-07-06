(function(blocks, element, i18n) {
  'use strict';

  var el = element.createElement;
  var registerBlockType = blocks.registerBlockType;
  var __ = i18n.__;

  registerBlockType('userspn/profile', {
    title: __('User Profile', 'userspn'),
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
      // This block is rendered server-side, so we return null
      return null;
    },
  });

})(
  window.wp.blocks,
  window.wp.element,
  window.wp.i18n
);

