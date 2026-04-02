(function($) {
	'use strict';

  $(document).ready(function() {
    if (window.USERSPN_Tooltips) { USERSPN_Tooltips.init(); }

    if ($('.userspn-select').length && $.fn.USERSPN_Selector) {
      $('.userspn-select').each(function(index) {
        if ($(this).attr('multiple') == 'true') {
          // For a multiple select
          $(this).USERSPN_Selector({
            multiple: true,
            searchable: true,
            placeholder: typeof userspn_i18n !== 'undefined' ? userspn_i18n.select_options : '',
          });
        }else{
          // For a single select
          $(this).USERSPN_Selector();
        }
      });
    }

    if ($.trumbowyg && typeof userspn_trumbowyg !== 'undefined' && $('.userspn-wysiwyg').length) {
      $.trumbowyg.svgPath = userspn_trumbowyg.path;
      $('.userspn-wysiwyg').each(function(index, element) {
        $(this).trumbowyg();
      });
    }
  });
})(jQuery);
