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
			$( '#selected-date' ).text( date.next );
			$( '#selected-date' ).text( dateObject['formatted'] );
		}

		if ( $(this).hasClass('prev-date') )
		{
			$( '#selected-date' ).text( date.prev );
			$( '#selected-date' ).text( dateObject['formatted'] );
		}
	})

	var date = {

		init: function() {

			date = new Date;

			date.next = this.next.bind( this );
			date.prev = this.prev.bind( this );

			dateObject = {
				date: date,
				formatted: moment(date).format('LL')
			};

			$( '#selected-date' ).text( dateObject["formatted"] );

		},

		next: function() {
			date.setDate(date.getDate() + 1);

			dateObject = {
				date: date,
				formatted: moment(date).format('LL')
			};

			return dateObject;
		},

		prev: function() {
			date.setDate(date.getDate() - 1);

			dateObject = {
				date: date,
				formatted: moment(date).format('LL')
			};

			return dateObject;
		}

	};

	date.init();


});