/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

(function($) {

	let feedRunButton = $('#pc-feed-run-button');
	let feedPopupButton = $('#pc-feeds-popup-button');

	let feedRunObject = {
		runFeedUrl: $("#pc-feed-run-url").val(),
		progressFeedUrl: $("#pc-feed-progress-url").val(),
		messageContainer: $('#pc-statusMessage'),
		chkProducts: $('#pc-chkProducts'),
		chkCategories: $('#pc-chkCategories'),
		chkUsers: $('#pc-chkUsers'),
		chkOrders: $('#pc-chkOrders'),
		feedButtonNotEnabled: $('#pc-feeds-button-not-enabled'),
		feedButtonManually: $('#pc-feeds-button-manually'),
		statusLabelDefault: $('#pc-feeds-label-base'),
		statusLabelProducts: $('#pc-productFeedStatusLabel'),
		statusLabelCategories: $('#pc-categoryFeedStatusLabel'),
		statusLabelUsers: $('#pc-userFeedStatusLabel'),
		statusLabelOrders: $('#pc-ordersFeedStatusLabel'),
		statusClassProducts: $('#pc-productFeedStatusClass'),
		statusClassCategories: $('#pc-categoryFeedStatusClass'),
		statusClassUsers: $('#pc-userFeedStatusClass'),
		statusClassOrders: $('#pc-ordersFeedStatusClass'),
		progressCheckRunning: 0,
	};

	function pcFeedRun()
	{
		if (!feedRunObject.chkProducts.is(':checked') &&
			!feedRunObject.chkCategories.is(':checked') &&
			!feedRunObject.chkUsers.is(':checked') &&
			!feedRunObject.chkOrders.is(':checked')
		) {
			return;
		}

		feedRunObject.chkProducts.prop("disabled", true);
		feedRunObject.chkCategories.prop("disabled", true);
		feedRunObject.chkUsers.prop("disabled", true);
		feedRunObject.chkOrders.prop("disabled", true);
		feedRunObject.isComplete = false;

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				product: feedRunObject.chkProducts.is(':checked'),
				category: feedRunObject.chkCategories.is(':checked'),
				user: feedRunObject.chkUsers.is(':checked'),
				orders: feedRunObject.chkOrders.is(':checked'),
				action: 'pureclarity_request_feeds',
				security: $('#pureclarity-request-feeds-nonce').val()
			},
		}).done(function(response) {
			pcInitProgress();
			if (feedRunObject.progressCheckRunning === 0) {
				setTimeout(pcFeedProgressCheck, 1000);
			}
		}).fail(function(jqXHR, status, err) {
			$('#pc-sign-up-response-holder').html('Error: Please reload the page and try again E2').addClass('pc-error-response');
		});
	}

	function pcInitProgress() {
		if (feedRunObject.chkProducts.is(':checked')) {
			feedRunObject.statusLabelProducts.html(feedRunObject.statusLabelDefault.val());
			feedRunObject.statusClassProducts.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkCategories.is(':checked')) {
			feedRunObject.statusLabelCategories.html(feedRunObject.statusLabelDefault.val());
			feedRunObject.statusClassCategories.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkUsers.is(':checked')) {
			feedRunObject.statusLabelUsers.html(feedRunObject.statusLabelDefault.val());
			feedRunObject.statusClassUsers.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkOrders.is(':checked')) {
			feedRunObject.statusLabelOrders.html(feedRunObject.statusLabelDefault.val());
			feedRunObject.statusClassOrders.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		feedPopupButton.addClass('pc-disabled');
	}

	function pcFeedProgressCheck() {
		feedRunObject.progressCheckRunning = 1;
		$.get(
			ajaxurl,
			{
				action: 'pureclarity_feed_progress',
				'security': $('#pureclarity-feed-progress-nonce').val()
			},
			function(response) {
				if (!response){
					// session has ended, reload to force login
					location.reload();
				} else {
					feedRunObject.statusLabelProducts.html(response.product.label);
					feedRunObject.statusLabelCategories.html(response.category.label);
					feedRunObject.statusLabelUsers.html(response.user.label);
					feedRunObject.statusLabelOrders.html(response.orders.label);
					feedRunObject.statusClassProducts.attr('class', 'pc-feed-status-icon ' + response.product.class);
					feedRunObject.statusClassCategories.attr('class', 'pc-feed-status-icon ' + response.category.class);
					feedRunObject.statusClassUsers.attr('class', 'pc-feed-status-icon ' + response.user.class);
					feedRunObject.statusClassOrders.attr('class', 'pc-feed-status-icon ' + response.orders.class);

					if (response.product.running ||
						response.category.running ||
						response.user.running ||
						response.orders.running
					) {
						setTimeout(pcFeedProgressCheck, 1000);
					} else if (response.product.enabled === false &&
						response.category.enabled === false &&
						response.user.enabled === false &&
						response.orders.enabled === false
					) {
						feedRunObject.progressCheckRunning = 0;
						pcFeedResetState();
						feedPopupButton.addClass('pc-disabled');
						feedPopupButton.attr('title', feedRunObject.feedButtonNotEnabled.val());
						feedPopupButton.html(feedRunObject.feedButtonNotEnabled.val());
					} else {
						var welcomeBanner = $('#pc-banner-welcome');
						if (welcomeBanner) {
							welcomeBanner.hide(1000, function (){
								$('#pc-banner-getting-started').show(1000);
							});
						}
						feedRunObject.progressCheckRunning = 0;
						pcFeedResetState();
						feedPopupButton.attr('title', feedRunObject.feedButtonManually.val());
						feedPopupButton.html(feedRunObject.feedButtonManually.val());
						feedPopupButton.removeClass('pc-disabled');
					}
				}
			}
		).fail(function(jqXHR, status, err) {
			feedRunObject.progressCheckRunning = 0;
			alert('Error: Please reload the page and try again E1');
		});
	}

	function pcFeedResetState() {
		feedRunObject.isComplete = true;
		feedRunObject.chkProducts.prop("disabled", false);
		feedRunObject.chkCategories.prop("disabled", false);
		feedRunObject.chkUsers.prop("disabled", false);
		feedRunObject.chkOrders.prop("disabled", false);
	}

	function pcSwitchMode(mode) {
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				mode: mode,
				action: 'pureclarity_switch_mode',
				security: $('#pureclarity-switch-mode-nonce').val()
			},
		}).done(function(response) {
			location.reload();
		}).fail(function(jqXHR, status, err) {
			location.reload();
		});
	}

	let modeChangeButtonLive = $('#pc-mode-go-live-button');
	let modeChangeButtonAdmin = $('#pc-mode-admin-only-button');
	let modeChangeButtonDisabled = $('#pc-mode-disabled-button');

	function pcNextStepsAction(action) {
		if (action.hasClass('pureclarity-clicked') === false) {
			var linkId = action.attr('id');
			$.post(
				ajaxurl,
				{
					action: 'pureclarity_complete_next_step',
					action_id: linkId,
					security: $('input[name=pureclarity-complete-next-step-nonce]').val()
				},
				function(data) {
					action.addClass('pureclarity-clicked');
					action.click();
				}
			).error(function() {
				action.addClass('pureclarity-clicked');
				action.click();
			}).fail(function() {
				action.addClass('pureclarity-clicked');
				action.click();
			});
			return false;
		}
	}


	$(document).ready(function() {

		feedsInProgress = $('#pc-feeds-in-progress');
		if ( feedsInProgress && feedsInProgress.val() === 'true' ) {
			pcFeedProgressCheck();
		}

		if (feedRunButton.length) {
			feedRunButton.on('click', function () {
				tb_remove();
				pcFeedResetState();
				pcFeedRun();
			});
		}

		if (modeChangeButtonLive.length) {
			modeChangeButtonLive.on('click', function () {
				pcSwitchMode('live');
			});
		}

		if (modeChangeButtonAdmin.length) {
			modeChangeButtonAdmin.on('click', function () {
				pcSwitchMode('admin');
			});
		}

		if (modeChangeButtonDisabled.length) {
			modeChangeButtonDisabled.on('click', function () {
				pcSwitchMode('disabled');
			});
		}

		$('.pc-action').on('click', function () {
			pcNextStepsAction($(this));
		});

		$('#pureclarity-headline-stat-today').addClass('pureclarity-headline-stat-active');


		$('.pureclarity-headline-stat-tab').on('click', function () {
			$('.pureclarity-headline-stat-tab').each(function(){
				$(this).removeClass('pureclarity-headline-stat-active');
			})
			$(this).addClass('pureclarity-headline-stat-active');
			var pcStatContentId = $(this).attr('id');
			$('.pureclarity-headline-stat').hide();
			$('#' + pcStatContentId + '-content').show();
		});

	});

})(jQuery);