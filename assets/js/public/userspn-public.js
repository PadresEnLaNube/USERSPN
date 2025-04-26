(function($) {
	'use strict';

	function userspn_timer(step) {
		var step_timer = $('.userspn-player-step[data-userspn-step="' + step + '"] .userspn-player-timer');
		var step_icon = $('.userspn-player-step[data-userspn-step="' + step + '"] .userspn-player-timer-icon');
		
		if (!step_timer.hasClass('timing')) {
			step_timer.addClass('timing');

      setInterval(function() {
      	step_icon.fadeOut('fast').fadeIn('slow').fadeOut('fast').fadeIn('slow');
      }, 5000);

      setInterval(function() {
      	step_timer.text(Math.max(0, parseInt(step_timer.text()) - 1)).fadeOut('fast').fadeIn('slow').fadeOut('fast').fadeIn('slow');
      }, 60000);
		}
	}

	$(document).on('click', '.userspn-popup-player-btn', function(e){
  	userspn_timer(1);
	});

  $(document).on('click', '.userspn-steps-prev', function(e){
    e.preventDefault();

    var steps_count = $('#userspn-recipe-wrapper').attr('data-userspn-steps-count');
    var current_step = $('#userspn-popup-steps').attr('data-userspn-current-step');
    var next_step = Math.max(0, (parseInt(current_step) - 1));
    
    $('.userspn-player-step').addClass('userspn-display-none-soft');
    $('#userspn-popup-steps').attr('data-userspn-current-step', next_step);
    $('.userspn-player-step[data-userspn-step=' + next_step + ']').removeClass('userspn-display-none-soft');

    if (current_step <= steps_count) {
    	$('.userspn-steps-next').removeClass('userspn-display-none');
    }

    if (current_step <= 2) {
    	$(this).addClass('userspn-display-none');
    }

    userspn_timer(next_step);
	});

	$(document).on('click', '.userspn-steps-next', function(e){
    e.preventDefault();

    var steps_count = $('#userspn-recipe-wrapper').attr('data-userspn-steps-count');
    var current_step = $('#userspn-popup-steps').attr('data-userspn-current-step');
    var next_step = Math.min(steps_count, (parseInt(current_step) + 1));

    $('.userspn-player-step').addClass('userspn-display-none-soft');
    $('#userspn-popup-steps').attr('data-userspn-current-step', next_step);
    $('.userspn-player-step[data-userspn-step=' + next_step + ']').removeClass('userspn-display-none-soft');

    if (current_step >= 1) {
    	$('.userspn-steps-prev').removeClass('userspn-display-none');
    }

    if (current_step >= (steps_count - 1)) {
    	$(this).addClass('userspn-display-none');
    }

    userspn_timer(next_step);
	});

	$(document).on('click', '.userspn-ingredient-checkbox', function(e){
    e.preventDefault();

    if ($(this).text() == 'radio_button_unchecked') {
    	$(this).text('task_alt');
    }else{
    	$(this).text('radio_button_unchecked');
    }
	});

	$('.userspn-carousel-main-images .owl-carousel').owlCarousel({
    margin: 10,
    center: true,
    nav: false, 
    autoplay: true, 
    autoplayTimeout: 5000, 
    autoplaySpeed: 2000, 
    pagination: true, 
    responsive:{
      0:{
        items: 2,
      },
      600:{
        items: 3,
      },
      1000:{
        items: 4,
      }
    }, 
  });
})(jQuery);
