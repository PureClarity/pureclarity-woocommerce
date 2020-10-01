/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

(function($) {

	$(document).ready(function() {
		$("#deactivate-pureclarity-for-woocommerce").click(function(){
			$.post(
				ajaxurl,
				{
					action: 'pureclarity_deactivate_feedback',
					reason: $('input[name=pureclarity_feedback_reason]:checked').val(),
					notes: $('#pureclarity_feedback_notes').val(),
					security: $('input[name=pureclarity_deactivate_feedback_nonce]').val()
				},
				function(data) {
					if (data.success) {
						tb_remove();
						$('.pc-signup-boxes').fadeOut(200, function () {
							$('#pc-waiting').fadeIn(200);
						});
						currentState = 'waiting';
						setTimeout(checkStatus, 5000);
					} else {
						$('#pc-sign-up-response-holder').html(data.error).addClass('pc-error-response');
					}
				}
			);
		});
		$("#cancel-deactivate-pureclarity-for-woocommerce").click(function(){
			tb_remove();
		});
	});

})(jQuery);