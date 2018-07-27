(function($) {

    var $buttons = $('.pureclarity-buttons ');
    $(document).on("click", ".pureclarity-product-datafeed", function() { runFeed("product"); } );
    $(document).on("click", ".pureclarity-category-datafeed", function() { runFeed("category"); } );
    $(document).on("click", ".pureclarity-brand-datafeed", function() { runFeed("brand"); } );
	$(document).on("click", ".pureclarity-user-datafeed", function() { runFeed("user"); } );
	$(document).on("click", ".pureclarity-order-datafeed", function() { runFeed("order"); } );


    function runFeed(type, currentPage) {
        $('.pureclarity-buttons ').prop("disabled", true);
        $('.pureclarity-message ').hide();
        updateMessage(type, "Running feed...");


        if (!currentPage) {
			currentPage = 1;
		}

        var data = {
			'action': 'pureclarity_run_datafeed',
            'page': currentPage,
            'type': type
        };

		$.post(ajaxurl, data, 
			function(response) {
				
				if (response && response.error){
					updateMessage( type, response.error );
					resetFeedProcess( type );
					return;
				}

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

				if (response.finished !== true) {
                    
					//reIndex( type, index, ++currentPage );
					resetFeedProcess( type );
				} else {
					updateMessage(type, 'Data Feed generation complete.');
					resetFeedProcess( type );
				}
			}
		).fail(
			function(response) {
				updateMessage( type, response.responseText );
				resetFeedProcess( type );
			}
		);
    }

    function updateMessage(type, message) {
		$('#pureclarity-' + type + '-message ').html(message);
		$('#pureclarity-' + type + '-message ').show();
    }

    function resetFeedProcess(type) {
        $('.pureclarity-buttons ').removeAttr( 'disabled' );
	}


})(jQuery);