/**
 * PorterBuddy WooCommerce cart & checkout widget functions
 */
jQuery( function( $ ) {

	// scope outside for all functions
	var availableDates;
	// set js moment library locale
	moment.locale("nb_NO");
	/**
	 * Create a date object for the widget
	 */
	var date = 
	{
		init: function() 
		{
			date = new Date;

			date.set = this.set.bind( this );
			date.next = this.next.bind( this );
			date.prev = this.prev.bind( this );

			dateObject = 
			{
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('ddd Do MMM')
			};
		},

		set: function( dateToSet ) 
		{
			date.setFullYear( moment(dateToSet).format("YYYY") );
			date.setMonth( moment(dateToSet).format("M")-1 );
			date.setDate( moment(dateToSet).format("D") );

			dateObject = 
			{
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('ddd Do MMM')
			};

			return dateObject;
		},

		next: function() 
		{
			date.setDate(date.getDate() + 1);

			dateObject = 
			{
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('ddd Do MMM')
			};

			return dateObject;
		},

		prev: function() 
		{
			date.setDate(date.getDate() - 1);

			dateObject = 
			{
				date: date,
				iso: moment(date).format(),
				formatted: moment(date).format('ddd Do MMM')
			};

			return dateObject;
		},
	};

	// initiate date variable
	date.init();

	// due to how woo handles cart updates, we wrap everything into a function
	function porterbuddy ()
	{
		/**
		 * When loaded, check availability
		 */
		ready('#porterbuddy-widget', function() 
		{
		    getAvailability( this );
		});


		/**
		 * Get available timeslots from the API (via backend)
		 */
		function getAvailability ( element )
		{
			$.ajax(
			{
				url: pbWidgetPHP['ajaxEndpoint'],
				type: 'GET',
				dataType: 'json',
				cache: true,
				data:
				{
					//action: '',
				},
				beforeSend: function () 
				{
					block( $( 'div.porterbuddy-widget' ) );
				},
				complete: function ()
				{
					unblock( $( 'div.porterbuddy-widget' ) );
				},
				success: function ( response )
				{
					if ( response['data']['delivery'] != undefined )
					{
						// popuplate available dates from API
						populateTimeslots( '#timeslots', response['data'] );
						// set shipping session data if not available already and update navigational control for timeslots
						getShippingSelection( upDateTimesControl, setShippingSelection );
					} 
					else
					{
						return false;
					}
				},
				error: function ( error )
				{
					return false;
				},
			})
		};

		
		/**
		 * Populate timeslots with data from the API
		 */
		function populateTimeslots ( element, data )
		{

			if ( data.express == undefined ) data.express = [];
			if ( data.delivery == undefined ) data.delivery = [];
			// merge express and regular deliveries
			var deliveryDates = $.merge($.merge([], data.express), data.delivery);

			deliveryDates.shift();

			// set first and last available date
			var firstAvailableDate = $(deliveryDates).get(0).start;
			var lastAvailableDate = $(deliveryDates).get(-1).start;
			availableDates = {firstAvailableDate, lastAvailableDate};


			// delete any previous divs
			$(element).empty();

			// if no available slots, return false
			if ( firstAvailableDate == undefined ) return false;

			// set date to first available slot and make available
			$( '#selected-date' ).text( moment(availableDates['firstAvailableDate']).format('ddd Do MMM') ).removeClass('unavailable');
			
			// set date object to available
			date.set( moment(availableDates['firstAvailableDate']).format('YYYY-MM-DD') );


			// add available express timeslots
			$.each( data.express, function() 
			{
				$('<div/>', 
				{
				    "class": 'porterbuddy-widget-timeslot ',
				    html: '<h6>' + 'Express' + '</h6>' + 
				    	'<p><span class="price">' + this.price.string + '</span></p>',
				    click: function() 
				    {
				    	// set active class on click
				    	$( this ).toggleClass( "active" ).siblings().removeClass( "active" );
				    }
				}).attr('data-value', 'pbdelivery_'+this.start+'_'+this.end)
					.attr('timeslot', this.start)
					.attr('type', 'express')
					.appendTo(element);
			});

			// add delivery timeslots and add metadata for filtration
			$.each( data.delivery, function() 
			{
				// hide those elements not to be shown today
				hidden = "";
				if ( ! moment(this.start).isSame(moment(availableDates['firstAvailableDate']), 'day') )
				{
					hidden = "porterbuddy-hide";
				}

				$('<div/>', 
				{
				    "class": 'porterbuddy-widget-timeslot ' + hidden,
				    html: '<h6>' + moment(this.start).locale("nb_NO").format("LT") + ' - ' + moment(this.end).format("LT") + '</h6>' + 
				    	'<p><span class="price">' + this.price.string + '</span></p>',
				    click: function() 
				    {
				    	// set active class on click
				    	$( this ).toggleClass( "active" ).siblings().removeClass( "active" );
				    }
				}).attr('data-value', 'pbdelivery_'+this.start+'_'+this.end)
					.attr('timeslot', this.start)
					.attr('type', "delivery")
					.appendTo(element);
			});

			return availableDates;
		}


		/**
		 * Update which timeslots to display, and navigational controls.
		 */
		function upDateTimesControl ( dateGiven )
		{
			// get date if set, or fetch from dateobject
			if ( dateGiven != undefined )
			{
				var dateChosen = moment( dateGiven ).format('YYYY-MM-DD');
				$( '#selected-date' ).text( date.set(dateChosen) );
			}
			else
			{
				var dateChosen = moment( dateObject.iso ).format('YYYY-MM-DD');
			}

			// format dates to same time 
			dateChosen = moment( dateChosen ).format();
			var dateFirst = moment( availableDates['firstAvailableDate'] ).format('YYYY-MM-DD');
			dateFirst = moment( dateFirst ).format();
			var dateLast = moment( availableDates['lastAvailableDate'] ).format('YYYY-MM-DD');
			dateLast = moment( dateLast ).format();


			// set date
			$( '#selected-date' ).text( dateObject['formatted'] );
			

			// set date prev selector 
			if ( moment( dateChosen ).isAfter( moment(dateFirst),'day') == true )
			{
				$('.porterbuddy-widget-date-selectors .prev-date').removeClass('unavailable');
			}
			else 
			{
				$('.porterbuddy-widget-date-selectors .prev-date').addClass('unavailable');
			}

			// set date next selector 
			if ( moment( dateChosen ).isBefore( moment(dateLast),'day') == true ) 
			{
				$('.porterbuddy-widget-date-selector.next-date').removeClass('unavailable');
			}
			else 
			{
				$('.porterbuddy-widget-date-selectors .next-date').addClass('unavailable');
			}


			// hide all slots
			var allSlots = $('div[data-value^="pbdelivery_"]');
			if ( allSlots[0] )
			{
				// display active date's options
				$.each(allSlots, function() 
				{
					$(this).addClass('porterbuddy-hide');
				});
			}

			// show new timeslots
			var slots = $('div[data-value^="pbdelivery_'+moment( dateChosen ).format('YYYY-MM-DD')+'"]');
			if ( slots[0] )
			{
				// display active date's options
				$.each(slots, function() 
				{
					$(this).removeClass('porterbuddy-hide');
				});
				
				// remove text saying it's empty
				$('#timeslots p.noSlots').remove();
			}
			else
			{
				if ( $('#timeslots p.noSlots').length < 1 )
				{
					$('#timeslots').prepend('<p class="noSlots">'+objectL10n.noSlotsAvailable+'</p>');
				}
			}

			// if no slots are active, set the first one active
			if ( $('.porterbuddy-widget-timeslot').hasClass('active') == false )
			{
				MakeActive = $('.porterbuddy-widget-timeslot').get(0);
				$(MakeActive).addClass('active');
				// save selection to session data
				setShippingSelection();
			}

			return true;

		}


		/**
		 * On "prev" and "next" click events, change data accordingly and update controls
		 */
		$( '.porterbuddy-widget-date-selectors' ).on("click", "a", function()
		{
			event.preventDefault();

			if ( $(this).hasClass('prev-date') && !$(this).hasClass('unavailable') )
			{
				$( '#selected-date' ).text( date.prev );
			}

			if ( $(this).hasClass('next-date') && !$(this).hasClass('unavailable') )
			{
				$( '#selected-date' ).text( date.next );
			}

			// update controls accordingly
			upDateTimesControl();
		})


		/**
		 * Update widget with session from PorterBuddy shipping selection
		 */
		 function getShippingSelection ( callback, callback )
		 {
			$.ajax(
			{
				url: pbWidgetPHP['ajaxphp'],
				type: 'GET',
				dataType: 'json',
				cache: false,
				data:
				{
					action: 'getShippingSelection',
				},
				beforeSend: function () 
				{
					block( $( 'div.porterbuddy-widget' ) );
				},
				complete: function ()
				{
					unblock( $( 'div.porterbuddy-widget' ) );
				},
				success: function ( response )
				{
					if ( response.status == "success" )
					{
						$('.porterbuddy-widget-timeslot[timeslot="' + response.pb_windowStart + '"]').addClass("active");
						$('label #porterbuddy_return').prop("checked", $.parseJSON(response.pb_returnOnDemand)); 
						$('label #porterbuddy_leave_doorstep').prop("checked", $.parseJSON(response.pb_leaveDoorStep)); 
						$('#porterbuddy_comment').val( response.pb_message );

						upDateTimesControl( response.pb_windowStart );
						updateTimeBlockPrices();
					}
					else
					{
						upDateTimesControl();
					}
				},
				error: function ( error )
				{
					return false;
				},
			})
		 }


		/**
		 * Update woo session with PorterBuddy shipping selection
		 */
		function setShippingSelection ()
		{
			var nonce = $('#porterbuddy-widget').data('wpnonce');
			var type = $('.porterbuddy-widget-timeslot.active').attr('type');
			var price = $('.porterbuddy-widget-timeslot.active .price').text();
			var windowStart = $('.porterbuddy-widget-timeslot.active').attr('timeslot');
			var returnOnDemand = $('#porterbuddy_return').prop("checked");
			var leaveDoorStep = $('#porterbuddy_leave_doorstep').prop("checked");
			var comment = $('#porterbuddy_comment').val();

			$.ajax(
			{
				url: pbWidgetPHP['ajaxphp'],
				type: 'POST',
				dataType: 'json',
				cache: false,
				data:
				{
					action: 'setShippingSelection',
					pb_nonce: nonce,
					pb_type: type,
					pb_windowStart: windowStart,
					pb_returnOnDemand: returnOnDemand,
					pb_leaveDoorStep: leaveDoorStep,
					pb_message: comment,
				},
				beforeSend: function () 
				{
					block( $( 'div.porterbuddy-widget' ) );
				},
				complete: function ()
				{
					unblock( $( 'div.porterbuddy-widget' ) );

					if($('#order_review').length > 0)
					{
                        $.ajax(
                            {
                                url: pbWidgetPHP['ajaxphp'],
                                type: 'POST',
                                dataType: 'json',
                                cache: false,
                                data:
                                    {
                                        action: 'pb_kill_shipping_cost_cache'
                                    },
                                success: function () {
                                    $.ajax(
                                        {
                                            url: pbWidgetPHP['ajaxphp'],
                                            type: 'POST',
                                            dataType: 'json',
                                            cache: false,
                                            data:
                                                {
                                                    action: 'woocommerce_update_order_review',
                                                    security: wc_checkout_params.update_order_review_nonce
                                                },
                                            success: function (response)
                                            {
                                                $( '#order_review .shop_table' ).replaceWith( response.fragments['.woocommerce-checkout-review-order-table'] );
                                                $( 'body' ).trigger( 'update_checkout' );
                                            }
                                        }
                                    );
                                }
                            }
                        );
                    }
                    else
                    {
                        var pb_update_cart_totals_div = function( html_str ) {
                            var table = $(html_str).find('.shop_table');
                            $( '.cart_totals .shop_table' ).replaceWith( table );
                            $( document.body ).trigger( 'updated_cart_totals' );
                        };

                        $.ajax( {
                            url:      pbWidgetPHP['ajaxwoo'] + 'get_cart_totals',
                            dataType: 'html',
                            success:  function( response ) {
                                pb_update_cart_totals_div( response );
                            },
                            complete: function() {
                                unblock( $( 'div.cart_totals' ) );
                            }
                        } );
                    }

				},
				success: function ( response )
				{
					//console.log( response );
					return true;
				},
				error: function ( error )
				{
					//console.log( error );
					return false;
				},
			})
		}

		/**
		 * function to update timeslot prices if "return on-delivery" is checked
		 */
		function updateTimeBlockPrices ()
		{
			if ( $('#porterbuddy_return').prop("checked") == true )
			{
				$('.porterbuddy-widget-timeslot').each( function()
				{
					if ( $( this ).data('returnPrice') == undefined || $( this ).data('returnPrice') === 0 )
					{
						let price = parseFloat( $('.price', this).text() );
						let extraPrice = parseFloat( $('.price', '.porterbuddy-widget-return').text() );
						
						$('.price', this).text(price+extraPrice);
						$(this).data('returnPrice',1);
					}
				});
			} 
			else
			{
				$('.porterbuddy-widget-timeslot').each( function()
				{
					if ( $( this ).data('returnPrice') === 1 )
					{
						let price = parseFloat( $('.price', this).text() );
						let extraPrice = parseFloat( $('.price', '.porterbuddy-widget-return').text() );
						
						$('.price', this).text(price-extraPrice);
						$(this).data('returnPrice',0);
					}
				});
			}
		}


		/**
		 * Update woo session with PorterBuddy shipping selection
		 */
		$( '#porterbuddy-widget' ).on(
			'click',
			'label #porterbuddy_return, label #porterbuddy_leave_doorstep, .porterbuddy-widget-timeslot',
			function () 
			{
				// update timeblock prices
				updateTimeBlockPrices();
				// set shipping selection
				setShippingSelection();	
			}
		);
		$( '#porterbuddy-widget' ).on(
			'blur',
			'.porterbuddy-widget-comment',
			function () 
			{
				setShippingSelection();
			}
		);
		$( '.checkout-button, #place_order').on(
			'click',
			function() 
			{
				setShippingSelection();
			}
		);
	}

	// initiate porterbuddy widget
	porterbuddy();
	
	/**
	 * after woo cart updates with ajax, reinitiate widget
	 */

	 $( document.body ).on( 'updated_cart_totals', function(){
	 	//porterbuddy();
	 	//console.log('trigggggad!');
	 });

});
