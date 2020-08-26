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
		chkBrands: $('#pc-chkBrands'),
		chkUsers: $('#pc-chkUsers'),
		chkOrders: $('#pc-chkOrders'),
		statusLabelProducts: $('#pc-productFeedStatusLabel'),
		statusLabelCategories: $('#pc-categoryFeedStatusLabel'),
		statusLabelBrands: $('#pc-brandFeedStatusLabel'),
		statusLabelUsers: $('#pc-userFeedStatusLabel'),
		statusLabelOrders: $('#pc-ordersFeedStatusLabel'),
		statusClassProducts: $('#pc-productFeedStatusClass'),
		statusClassCategories: $('#pc-categoryFeedStatusClass'),
		statusClassBrands: $('#pc-brandFeedStatusClass'),
		statusClassUsers: $('#pc-userFeedStatusClass'),
		statusClassOrders: $('#pc-ordersFeedStatusClass'),
		progressCheckRunning: 0,
	};

	/**
	 * Copyright © PureClarity. All rights reserved.
	 * See LICENSE.txt for license details.
	 */

	let currentState = $('#pc-current-state').val();
	let signUpSubmitButton = $('#pc-sign-up-submit-button');
	let signupForm = $('#pc-sign-up-form');
	let saveDetailsForm = $('#pc-save-details-form');
	let saveDetailsButton = $('#pc-save-details-button');

	function submitSignUp()
	{
		let isValid = true;
		// First name
		let firstname = $('#pc-sign-up-firstname');
		let firstnameErr = $('#pc-sign-up-firstname-error');
		if (firstname.val() === '') {
			isValid = false;
			firstname.addClass('pc-error');
			firstnameErr.css('display', 'inline-block');
		} else {
			firstname.removeClass('pc-error');
			firstnameErr.css('display', 'none');
		}

		// Last name
		let lastname = $('#pc-sign-up-lastname');
		let lastnameErr = $('#pc-sign-up-lastname-error');
		if (lastname.val() === '') {
			isValid = false;
			lastname.addClass('pc-error');
			lastnameErr.css('display', 'inline-block');
		} else {
			lastname.removeClass('pc-error');
			lastnameErr.css('display', 'none');
		}

		// Email

		var regex_email =/^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,3})$/;
		let email = $('#pc-sign-up-email');
		let emailErr = $('#pc-sign-up-email-error');
		if (regex_email.test(email.val()) === false || email.val() === '') {
			isValid = false;
			email.addClass('pc-error');
			emailErr.css('display', 'inline-block');
		} else {
			email.removeClass('pc-error');
			emailErr.css('display', 'none');
		}

		// Company
		let company = $('#pc-sign-up-company');
		let companyErr = $('#pc-sign-up-company-error');
		if (company.val() === '') {
			isValid = false;
			company.addClass('pc-error');
			companyErr.css('display', 'inline-block');
		} else {
			company.removeClass('pc-error');
			companyErr.css('display', 'none');
		}

		// Password
		var regex_password = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/;
		let password = $('#pc-sign-up-password');
		let passwordErr = $('#pc-sign-up-password-error');
		if (regex_password.test(password.val()) === false || password.val() === '') {
			isValid = false;
			password.addClass('pc-error');
			passwordErr.css('display', 'inline-block');
		} else {
			password.removeClass('pc-error');
			passwordErr.css('display', 'none');
		}


		// Store Name
		let storename = $('#pc-sign-up-store-name');
		let storenameErr = $('#pc-sign-up-store-name-error');
		if (storename.val() === '') {
			isValid = false;
			storename.addClass('pc-error');
			storenameErr.css('display', 'inline-block');
		} else {
			storename.removeClass('pc-error');
			storenameErr.css('display', 'none');
		}

		// URL
		let storeurl = $('#pc-sign-up-store-url');
		let storeurlErr = $('#pc-sign-up-store-url-error');
		let url_regex = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/

		if (url_regex.test(storeurl.val()) === false || storeurl.val() === '') {
			isValid = false;
			storeurl.addClass('pc-error');
			storeurlErr.css('display', 'inline-block');
		} else {
			storeurl.removeClass('pc-error');
			storeurlErr.css('display', 'none');
		}

		// Timezone
		let timezone = $('#pc-sign-up-timezone');
		let timezoneErr = $('#pc-sign-up-timezone-error');
		if (timezone.val() === '') {
			isValid = false;
			timezone.addClass('pc-error');
			timezoneErr.css('display', 'inline-block');
		} else {
			timezone.removeClass('pc-error');
			timezoneErr.css('display', 'none');
		}

		// Region
		let region = $('#pc-sign-up-region');
		let regionErr = $('#pc-sign-up-region-error');
		if (region.val() === '') {
			isValid = false;
			region.addClass('pc-error');
			regionErr.css('display', 'inline-block');
		} else {
			region.removeClass('pc-error');
			regionErr.css('display', 'none');
		}

		if (isValid) {
			$.post(ajaxurl, signupForm.serialize(), function(data) {
				if (data.success) {
					tb_remove();
					$('#pc-welcome').fadeOut(200, function () {
						$('#pc-waiting').fadeIn(200);
					});
					currentState = 'waiting';
					setTimeout(checkStatus, 5000);
				} else {
					$('#pc-sign-up-response-holder').html(data.error).addClass('pc-error-response');
				}
			}).fail(function(jqXHR, status, err) {
				$('#pc-sign-up-response-holder').html('Error: Please reload the page and try again').addClass('pc-error-response');
			});
		}
	}

	function submitSaveDetails()
	{
		let isValid = true;
		// Access Key
		let accessKey = $('#pc-details-access-key');
		if (accessKey.val() === '') {
			isValid = false;
			accessKey.addClass('pc-error');
		} else {
			accessKey.removeClass('pc-error');
		}

		// Access Key
		let secretKey = $('#pc-details-secret-key');
		if (secretKey.val() === '') {
			isValid = false;
			secretKey.addClass('pc-error');
		} else {
			secretKey.removeClass('pc-error');
		}

		// Region
		let region = $('#pc-details-region');
		if (region.val() === '') {
			isValid = false;
			region.addClass('pc-error');
		} else {
			region.removeClass('pc-error');
		}

		if (isValid) {
			$('#pc-details-error').css('display', 'none');
			$.ajax({
				action: 'pureclarity_link_account',
				url: ajaxurl,
				data: saveDetailsForm.serialize(),
				type: "POST",
				dataType: 'json'
			}).done(function (data) {
				if (data.success) {
					$('#pc-welcome').fadeOut(200, function () {
						$('#pc-content').fadeIn(200);
					});
					currentState = 'configured';
					pcFeedProgressCheck();
				} else {
					alert('Please reload the page and try again');
				}
			}).fail(function(jqXHR, status, err) {
				alert('Please reload the page and try again');
			});
		} else {
			$('#pc-details-error').css('display', 'block');
		}
	}

	function checkStatus()
	{
		$.get(
			ajaxurl,
			{
				action: 'pureclarity_signup_progress'
			},
			function(data) {
				if (data.success) {
					$('#pc-waiting').fadeOut(200, function () {
						$('#pc-content').fadeIn(200);
					});
					currentState = 'configured';
                    feedRunObject.statusLabelProducts.html('Waiting for feed run to start');
                    feedRunObject.statusClassProducts.attr('class', 'pc-feed-status-icon pc-feed-waiting');
                    feedRunObject.statusLabelCategories.html('Waiting for feed run to start');
                    feedRunObject.statusClassCategories.attr('class', 'pc-feed-status-icon pc-feed-waiting');
                    feedRunObject.statusLabelUsers.html('Waiting for feed run to start');
                    feedRunObject.statusClassUsers.attr('class', 'pc-feed-status-icon pc-feed-waiting');
                    feedRunObject.statusLabelOrders.html('Waiting for feed run to start');
                    feedRunObject.statusClassOrders.attr('class', 'pc-feed-status-icon pc-feed-waiting');
                    pcFeedProgressCheck();
				} else if (data.error !== '') {
					alert(data.error);
				} else {
					setTimeout(checkStatus, 5000);
				}
			}
		).fail(function(jqXHR, status, err) {
			alert('Error: Please reload the page and try again');
		});
	}
	
	function pcFeedRun()
	{
		if (!feedRunObject.chkProducts.is(':checked') &&
			!feedRunObject.chkCategories.is(':checked') &&
			(feedRunObject.chkBrands.length === 0 || !feedRunObject.chkBrands.is(':checked')) &&
			!feedRunObject.chkUsers.is(':checked') &&
			!feedRunObject.chkOrders.is(':checked')
		) {
			return;
		}

		feedRunObject.chkProducts.prop("disabled", true);
		feedRunObject.chkCategories.prop("disabled", true);

		if (feedRunObject.chkBrands.length) {
			feedRunObject.chkBrands.prop("disabled", true);
		}

		feedRunObject.chkUsers.prop("disabled", true);
		feedRunObject.chkOrders.prop("disabled", true);
		feedRunObject.isComplete = false;

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				product: feedRunObject.chkProducts.is(':checked'),
				category: feedRunObject.chkCategories.is(':checked'),
				brand: feedRunObject.chkBrands.length && feedRunObject.chkBrands.is(':checked'),
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
			$('#pc-sign-up-response-holder').html('Error: Please reload the page and try again').addClass('pc-error-response');
		});
	}

	function pcInitProgress() {
		if (feedRunObject.chkProducts.is(':checked')) {
			feedRunObject.statusLabelProducts.html('Waiting for feed run to start');
			feedRunObject.statusClassProducts.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkCategories.is(':checked')) {
			feedRunObject.statusLabelCategories.html('Waiting for feed run to start');
			feedRunObject.statusClassCategories.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkBrands.length && feedRunObject.chkBrands.is(':checked')) {
			feedRunObject.statusLabelBrands.html('Waiting for feed run to start');
			feedRunObject.statusClassBrands.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkUsers.is(':checked')) {
			feedRunObject.statusLabelUsers.html('Waiting for feed run to start');
			feedRunObject.statusClassUsers.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		if (feedRunObject.chkOrders.is(':checked')) {
			feedRunObject.statusLabelOrders.html(__('Waiting for feed run to start'));
			feedRunObject.statusClassOrders.attr('class', 'pc-feed-status-icon pc-feed-waiting');
		}

		feedPopupButton.addClass('pc-disabled');
		feedPopupButton.attr('title', 'Feeds In Progress');
		feedPopupButton.html('Feeds In Progress');
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
					feedRunObject.statusLabelBrands.html(response.brand.label);
					feedRunObject.statusLabelUsers.html(response.user.label);
					feedRunObject.statusLabelOrders.html(response.orders.label);
					feedRunObject.statusClassProducts.attr('class', 'pc-feed-status-icon ' + response.product.class);
					feedRunObject.statusClassCategories.attr('class', 'pc-feed-status-icon ' + response.category.class);
					feedRunObject.statusClassBrands.attr('class', 'pc-feed-status-icon ' + response.brand.class);
					feedRunObject.statusClassUsers.attr('class', 'pc-feed-status-icon ' + response.user.class);
					feedRunObject.statusClassOrders.attr('class', 'pc-feed-status-icon ' + response.orders.class);

					if (response.product.running ||
						response.category.running ||
						response.brand.running ||
						response.user.running ||
						response.orders.running
					) {
						setTimeout(pcFeedProgressCheck, 1000);
					} else if (response.product.enabled === false &&
						response.category.enabled === false &&
						response.brand.enabled === false &&
						response.user.enabled === false &&
						response.orders.enabled === false
					) {
						feedRunObject.progressCheckRunning = 0;
						feedPopupButton.addClass('pc-disabled');
						feedPopupButton.attr('title', 'Feeds Not Enabled');
						feedPopupButton.html('Feeds Not Enabled');
					} else {
						feedRunObject.progressCheckRunning = 0;
						feedPopupButton.attr('title', 'Run Feeds Manually');
						feedPopupButton.html('Run Feeds Manually');
						feedPopupButton.removeClass('pc-disabled');
					}
				}
			}
		).fail(function(jqXHR, status, err) {
			alert('Error: Please reload the page and try again');
		});
	}

	function pcFeedResetState() {
		feedRunObject.isComplete = true;
		feedRunObject.chkProducts.prop("disabled", false);
		feedRunObject.chkCategories.prop("disabled", false);
		if (feedRunObject.chkBrands.length) {
			feedRunObject.chkBrands.prop("disabled", false);
		}
		feedRunObject.chkUsers.prop("disabled", false);
		feedRunObject.chkOrders.prop("disabled", false);
	}

	$(document).ready(function() {
		if (currentState === 'not_configured') {
			signUpSubmitButton.on('click', submitSignUp);
			saveDetailsButton.on('click', submitSaveDetails);
		}

		if (currentState === 'waiting') {
			checkStatus();
		}

		if( $('#pc-current-state').val() === 'configured' && $('#pc-feeds-in-progress').val() === '1') {
			pcFeedProgressCheck();
		}

		if (feedRunButton.length) {
			feedRunButton.on('click', function () {
				tb_remove();
				pcFeedResetState();
				pcFeedRun();
			});
		}
	});

})(jQuery);