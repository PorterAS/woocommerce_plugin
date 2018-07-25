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
		//getAvailability();

		if ( $(this).hasClass('next-date') )
		{
			console.log("next");
			$( '#selected-date' ).text( date.next );
			
		}

		if ( $(this).hasClass('prev-date') )
		{
			console.log("prev");
			$( '#selected-date' ).text( date.prev );
		}
	})

	var date = {

		init: function() {

			date = new Date;

			date.next = this.next.bind( this );
			date.prev = this.prev.bind( this );

			$( '#selected-date' ).text( moment(date).format('LL') );

		},

		next: function() {
			date.setDate(date.getDate() + 1);
			return moment(date).format('LL');
		},

		prev: function() {
			date.setDate(date.getDate() - 1);
			return moment(date).format('LL');
		}

	};

	date.init();


});