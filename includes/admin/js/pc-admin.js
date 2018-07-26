console.log("HELLO ADMIN!");


(function($) {

    var $buttons = $('.pureclarity-buttons ');
    $(document).on("click", ".pureclarity-product-datafeed", function() { runFeed("product"); } );
    $(document).on("click", ".pureclarity-category-datafeed", function() { runFeed("category"); } );
    $(document).on("click", ".pureclarity-brand-datafeed", function() { runFeed("brand"); } );
    $(document).on("click", ".pureclarity-user-datafeed", function() { runFeed("user"); } );


    function runFeed(type, currentPage) {
        $('.pureclarity-buttons ').prop("disabled", true);
        $('.pureclarity-message ').hide();
        $('#pureclarity-' + type + '-message ').show();


        if (!currentPage) {
			currentPage = 1;
		}

        var data = {
			'action': 'pureclarity_run_datafeed',
            'p': currentPage,
            'type': type
        };

		$.post(
			ajaxurl, data, function(response) {
				if (typeof response.totalPagesCount === 'undefined') {
                    updateMessage(type, 'An error occurred');
					resetFeedProcess( type );
					return;
				}

				if (response.totalPagesCount === 0) {
                    updateMessage(type, 'No items to work with.');
					resetFeedProcess( type );
					return;
				}
				progress = Math.round( (currentPage / response.totalPagesCount) * 100 );
				updateMessage( type, "Processing Feed... " + progress + "% done" );

				if (response.finished === true) {
                    updateMessage(type, 'Data Feed generation complete.');
					//reIndex( type, index, ++currentPage );
				} else {
					resetFeedProcess( type );
				}
			}
		).fail(
			function(response) {
				alert( 'An error occurred: ' + response.responseText );
				resetFeedProcess( type );
			}
		);
    }

    function updateMessage(type, message) {
        $('#pureclarity-' + type + '-message ').html(message);
    }

    function resetFeedProcess(type) {
        $('.pureclarity-buttons ').removeAttr( 'disabled' );
        $('#pureclarity-' + type + '-message ').show();
	}


})(jQuery);