(function($) {
	'use strict';

  $(document).ready(function() {
    if($('.userspn-tooltip').length) {
      $('.userspn-tooltip').tooltipster({maxWidth: 300, delayTouch:[0, 4000], customClass: 'userspn-tooltip'});
    }

    if ($('.userspn-select').length) {
      $('.userspn-select').each(function(index) {
        if ($(this).attr('multiple') == 'true') {
          // For a multiple select
          $(this).USERSPN_Selector({
            multiple: true,
            searchable: true,
            placeholder: userspn_i18n.select_options,
          });
        }else{
          // For a single select
          $(this).USERSPN_Selector();
        }
      });
    }

    $.trumbowyg.svgPath = userspn_trumbowyg.path;
    $('.userspn-wysiwyg').each(function(index, element) {
      $(this).trumbowyg();
    });
  });
})(jQuery);
