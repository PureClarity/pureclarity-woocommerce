/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

(function($) {

	$(document).ready(function() {
		$("#deactivate-pureclarity-for-woocommerce").click(function(){
			var linkUrl = $(this).attr('href');
			$.post(
				ajaxurl,
				{
					action: 'pureclarity_deactivate_feedback',
					reason: $('input[name=pureclarity_feedback_reason]:checked').val(),
					notes: $('#pureclarity_feedback_notes').val(),
					security: $('input[name=pureclarity_deactivate_feedback_nonce]').val()
				},
				function(data) {
					window.location.href = linkUrl;
				}
			).error(function() {
				window.location.href = linkUrl;
			}).fail(function() {
				window.location.href = linkUrl;
			});
			return false;
		});
		$("#cancel-deactivate-pureclarity-for-woocommerce").click(function(){
			tb_remove();
		});
	});

})(jQuery);