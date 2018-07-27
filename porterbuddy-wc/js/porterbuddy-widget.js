/*
 * PorterBuddy WooCommerce widget scripts
 */

jQuery( function( $ ) {

	// set locale
	moment.locale("nb_NO");

	function m_formatter ($date)
	{
		return moment($date).format('dddd Do MMM');
	}

	/**
	 * Create a date object for the widget
	 */
	var date = {

		init: function() {

			date = new Date;

			date.next = this.next.bind( this );
			date.prev = this.prev.bind( this );

			dateObject = {
				date: date,
				formatted: m_formatter(date)
			};

			$( '#selected-date' ).text( dateObject["formatted"] );

		},

		next: function() {
			date.setDate(date.getDate() + 1);

			dateObject = {
				date: date,
				formatted: m_formatter(date)
			};

			return dateObject;
		},

		prev: function() {
			date.setDate(date.getDate() - 1);

			dateObject = {
				date: date,
				formatted: m_formatter(date)
			};

			return dateObject;
		},

		formatter: function() {

			

		}

	};

	date.init();

	// When Porterbuddy widget is available
	ready('#porterbuddy-widget', function(element) {
	    
	    console.log("PorterBuddy loaded");

	    getAvailability( this );

	});

	// make the request 	
	function getAvailability ( element )
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
				showLoader( element );
			},
			complete: function ()
			{
				hideLoader( element );
			},
			success: function ( response )
			{
				populateTimeslots( '#timeslots', response['data'] );
			},
			error: function ( error )
			{
				//
			},
		})
	};


// 'data-value', 'pbdelivery_'+this.start+'_'+this.end)


	function populateTimeslots ( element, data )
	{

		$(element).empty();

		console.log(data);

		$.each( data, function() 
		{
			// add timeslot element for every timeslot returned by api
			$('<div/>', {
			    "class": 'porterbuddy-widget-timeslot',
			    html: '<h6>' + moment(this.start).locale("nb_NO").format("LT") + ' - ' + moment(this.end).format("LT") + '</h6>' + 
			    	'<p><span class="price">' + this.price.string + '</span></p>',
			}).attr('data-value', 'pbdelivery_'+this.start+'_'+this.end).appendTo(element);
		});

	}



	$( '.porterbuddy-widget-date-selectors' ).on("click", "a", function(){
		event.preventDefault();

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

});
