/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

(function($) {

	let currentState = $('#pc-current-state').val();
	let signUpSubmitButton = $('#pc-sign-up-submit-button');
	let signupForm = $('#pc-sign-up-form');
	let linkAccountForm = $('#pc-link-account-form');
	let linkAccountButton = $('#pc-link-account-submit-button');
	let signupSubmitted = false;

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

		if (isValid && !signupSubmitted) {
			signupSubmitted = true;
			$('#pc-sign-up').fadeOut(200, function () {
				$('#pc-waiting').fadeIn(200);
				$.post(ajaxurl, signupForm.serialize(), function(data) {
					if (data.success) {
						tb_remove();
						currentState = 'waiting';
						setTimeout(checkStatus, 5000);
					} else {
						$('#pc-waiting').fadeOut(200, function () {
							$('#pc-sign-up').fadeIn(200);
						});
						$('#pc-sign-up-response-holder').html(data.error).addClass('pc-error-response');
						signupSubmitted = false;
					}
				}).fail(function(jqXHR, status, err) {
					$('#pc-waiting').fadeOut(200, function () {
						$('#pc-sign-up').fadeIn(200);
					});
					$('#pc-sign-up-response-holder').html('Error: Please reload the page and try again').addClass('pc-error-response');
					signupSubmitted = false;
				});
			});

		}
	}

	function submitLinkAccount()
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
			$.post(ajaxurl, linkAccountForm.serialize(), function(data) {
				if (data.success) {
					$('#pc-link-account-response-holder').html('Account linked successfully').addClass('pc-success-response');
					location.reload();
				} else {
					$('#pc-link-account-response-holder').html(data.error).addClass('pc-error-response');
				}
			}).fail(function(jqXHR, status, err) {
				$('#pc-link-account-response-holder').html(err).addClass('pc-error-response');
			});
		} else {
			$('#pc-link-account-response-holder').html('Please fill in all fields').addClass('pc-error-response');
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
					location.reload();
				} else if (data.error !== '') {
					alert(data.error);
				} else {
					setTimeout(checkStatus, 5000);
				}
			}
		).fail(function(jqXHR, status, err) {
			alert('Error: Please reload the page and try again E4');
		});
	}
	
	$(document).ready(function() {
		if (currentState !== 'configured') {
			signUpSubmitButton.on('click', submitSignUp);
			linkAccountButton.on('click', submitLinkAccount);
			$('#pc-features-list').slick({
				dots: true,
				infinite: false,
				arrows: true,
				autoplay: true,
				autoplaySpeed: 5000,
				speed: 300,
				slidesToShow: 1,
				slidesToScroll: 1
			});
		}

		if (currentState === 'waiting') {
			checkStatus();
		}
	});

})(jQuery);