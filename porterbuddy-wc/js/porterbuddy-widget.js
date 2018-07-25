/*
 * PorterBuddy WooCommerce widget scripts
 */

jQuery( function( $ ) {
	
	//
	$.ajax({
		url: pbWidgetPHP['ajaxEndpoint'],
		type: 'GET',
		dataType: 'json',
		cache: false,
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

});