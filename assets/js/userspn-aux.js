(function($) {
	'use strict';

  $(document).ready(function() {
    if($('.userspn-tooltip').length && $.fn.tooltipster) {
      $('.userspn-tooltip').tooltipster({maxWidth: 300, delayTouch:[0, 4000], customClass: 'userspn-tooltip'});
    }

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
