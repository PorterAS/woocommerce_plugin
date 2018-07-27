/*
 * PorterBuddy WooCommerce widget scripts
 */

jQuery( function( $ ) {

	// set locale
	moment.locale("nb_NO");

	var availableDates;

	/**
	 * Create a date object for the widget
	 */
	var date = {

		init: function() {

			date = new Date;

			date.set = this.set.bind( this );
			date.next = this.next.bind( this );
			date.prev = this.prev.bind( this );

			dateObject = {
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('dddd Do MMM')
			};

			$( '#selected-date' ).text( dateObject["formatted"] );

		},

		set: function(day) {
			date.setDate(day);

			dateObject = {
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('dddd Do MMM')
			};

			return dateObject;
		},

		next: function() {
			date.setDate(date.getDate() + 1);

			dateObject = {
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('dddd Do MMM')
			};

			return dateObject;
		},

		prev: function() {
			date.setDate(date.getDate() - 1);

			dateObject = {
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('dddd Do MMM')
			};

			return dateObject;
		},
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
				populateTimeslots( '#timeslots', response['data'] ); // generates availableDates
				upDateTimesControl();
			},
			error: function ( error )
			{
				return null;
			},
		})
	};

	

	// populate timeslots into divs that are selectable
	function populateTimeslots ( element, data )
	{
		// set first and last available date
		var firstAvailableDate = $(data).get(1).start;
		var lastAvailableDate = $(data).get(-1).start;
		availableDates = {firstAvailableDate, lastAvailableDate};

		// delete any previous divs
		$(element).empty();

		// if no available slots, return false
		if ( firstAvailableDate == undefined ) return false;

		// set date to first available slot and make available
		$( '#selected-date' ).text( moment(availableDates['firstAvailableDate']).format('dddd Do MMM') ).removeClass('unavailable');

		// set date object to available
		date.set( moment(availableDates['firstAvailableDate']).format('D') ); // #RPT: might need work due to js setDate functionality

		// add timeslot element for every timeslot returned by api
		$.each( data, function() 
		{
			// hide those elements not to be shown today
			hidden = "";
			if ( ! moment(this.start).isSame(moment(availableDates['firstAvailableDate']), 'day') )
			{
				hidden = "porterbuddy-hide";
			}

			$('<div/>', {
			    "class": 'porterbuddy-widget-timeslot ' + hidden,
			    html: '<h6>' + moment(this.start).locale("nb_NO").format("LT") + ' - ' + moment(this.end).format("LT") + '</h6>' + 
			    	'<p><span class="price">' + this.price.string + '</span></p>',
			    click: function() {
			    	$( this ).toggleClass( "active" ).siblings().removeClass( "active" );
			    }
			}).attr('data-value', 'pbdelivery_'+this.start+'_'+this.end).attr('timeslot', this.start).appendTo(element);
		});

		return availableDates;
	}

	function upDateTimesControl ()
	{
		// format dates to same time 
		var dateChosen = moment( dateObject.iso ).format('YYYY-MM-DD');
		dateChosen = moment( dateChosen ).format();
		var dateFirst = moment( availableDates['firstAvailableDate'] ).format('YYYY-MM-DD');
		dateFirst = moment( dateFirst ).format();
		var dateLast = moment( availableDates['lastAvailableDate'] ).format('YYYY-MM-DD');
		dateLast = moment( dateLast ).format();


		// console.log("date.iso: " + dateChosen);
		// console.log("firstAvailableDate: " + dateFirst);
		// console.log("lastAvailableDate: " + dateLast);
		//console.log(  "datovalg er etter førstedato: " + moment( dateChosen ).isAfter( moment(dateFirst),'day')  );
		//console.log(  "datovalg er før sistedato: " + moment( dateChosen ).isBefore( moment(dateLast),'day')  );
		

		// set date prev selector 
		if ( moment( dateChosen ).isAfter( moment(dateFirst),'day') == true )
		{
			$('.porterbuddy-widget-date-selectors .prev-date').removeClass('unavailable');
		}
		else {
			$('.porterbuddy-widget-date-selectors .prev-date').addClass('unavailable');
		}

		// set date next selector 
		if ( moment( dateChosen ).isBefore( moment(dateLast),'day') == true ) 
		{
			$('.porterbuddy-widget-date-selector.next-date').removeClass('unavailable');
		}
		else {
			$('.porterbuddy-widget-date-selectors .next-date').addClass('unavailable');
		}
	}



	$( '.porterbuddy-widget-date-selectors' ).on("click", "a", function(){
		event.preventDefault();

		if ( $(this).hasClass('prev-date') && !$(this).hasClass('unavailable') )
		{
			$( '#selected-date' ).text( date.prev );
			$( '#selected-date' ).text( dateObject['formatted'] );
		}

		if ( $(this).hasClass('next-date') && !$(this).hasClass('unavailable') )
		{
			$( '#selected-date' ).text( date.next );
			$( '#selected-date' ).text( dateObject['formatted'] );
		}

		// update controls accordingly
		upDateTimesControl();

	})

});
