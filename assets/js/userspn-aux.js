(function($) {
	'use strict';

  $(document).ready(function() {
    if($('.userspn-tooltip').length) {
      $('.userspn-tooltip').tooltipster({maxWidth: 300, delayTouch:[0, 4000], customClass: 'userspn-tooltip'});
    }

    if ($('.userspn-select').length) {
      $('.userspn-select').each(function(index) {
        if ($(this).children('option').length < 7) {
          $(this).select2({minimumResultsForSearch: -1, allowClear: true});
        }else{
          $(this).select2({allowClear: true});
        }
      });
    }

    $.trumbowyg.svgPath = userspn_trumbowyg.path;
    $('.userspn-wysiwyg').each(function(index, element) {
      $(this).trumbowyg();
    });
  });
})(jQuery);
