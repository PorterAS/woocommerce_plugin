/*
 * PorterBuddy WooCommerce widget scripts
 */

jQuery( function( $ ) {

	// make the request 	
	function getAvailability ()
	{
		$.ajax({
			url: pbWidgetPHP['ajaxEndpoint'],
			type: 'GET',
			dataType: 'json',
			cache: true,
			data:
			{
				//action: 'getDays',
			},
			beforeSend: function () 
			{
				showLoader();
			},
			complete: function ()
			{
				hideLoader();
			},
			success: function ( response )
			{
				console.log( response );
			},
			error: function ( error )
			{
				//
			},
		})
	};

	$( '.porterbuddy-widget-date-selectors' ).on("click", "a", function(){
		event.preventDefault();
		console.log("hei");

		getAvailability();

	})

});