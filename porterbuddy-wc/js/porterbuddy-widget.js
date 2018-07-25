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
				//
			},
			complete: function ()
			{
				//
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

	function showLoader ( $this=false )
	{
		if ( $this ) {
			//
		} 
		$().addClass( 'isActive' );
	}
	
	function hideLoader ()
	{
		$(this).removeClass( 'isActive' );
	}	

});