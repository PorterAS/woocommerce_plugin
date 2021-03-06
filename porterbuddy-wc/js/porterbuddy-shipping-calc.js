/**
 * functions for shipping calculator embedded on product detail page
 */
jQuery( function( $ ) {

	/**
	 * Check location and act on node.
	 *
	 * @param {JQuery Object} $node
	 * @param {bool} $clear 
	 */
	var locationCheck = function( $node, $clear=false ) {

		// get post code from form, in case Woo has one set
		var currentPostCode = $('.shipping-calculator-form').find('input[name="calc_shipping_postcode"]');
		currentPostCodeOldVal = currentPostCode.val();

		var geoSetting = $('.woocommerce-shipping-calculator').data("geo");

		// clear out location data if true
		if ( $clear == true ) {
			//PBsetCookie('pb_country','x',1);
			PBsetCookie('pb_postcode','x',1);
			PBsetCookie('pb_location','x',1);
			currentPostCode.val('');
		}

		// if postcode is not set correctly
		if ( currentPostCode.val().length < 2 ) {

			if ( PBgetCookie('pb_location') ) {
				var curloc = PBgetCookie('pb_location');
			} else { var curloc = 0; }

			if ( curloc.length > 5 ) {
				window.location.assign(window.location.href + " ");
			}
			else if ( geoSetting.indexOf("yes") >= 0  && navigator.geolocation ) {

				// fetch location data and place in cookie for PHP handling

				function geo_success(position) {
					console.log("Location set");
					PBsetCookie('pb_location', '['+position.coords.latitude+','+position.coords.longitude+']', 30);
					window.location.reload();
				}

				function geo_error() {

					if (this.code == this.PERMISSION_DENIED) {

						console.log( 'Access to location is blocked by user.' );

						// set values
						$(currentPostCode).val(currentPostCodeOldVal);
						PBsetCookie('pb_postcode',currentPostCodeOldVal,60);

						geosubtn = $('.shipping-calculator-form').find('button[class="use_geo_btn"]');

						// disable button and tell user
						$(geosubtn).prop('disabled', true);
						$(geosubtn).after('<p class="porterbuddy-warning">'+objectL10n.geoError+'</p>');
						setTimeout(function(){
							$(geosubtn).next().remove();
						}, 6000);

					} else {
						console.log( 'Unable to get location.' );
						PBsetCookie('pb_postcode',currentPostCodeOldVal,60);
					}
					// give access to form again
					unblock( $node );
				}

				navigator.geolocation.getCurrentPosition(geo_success, geo_error);
			}
			else {
				console.log( 'Could not get location.' );
				PBsetCookie('pb_postcode',currentPostCodeOldVal,60);
				unblock( $node );
			}

		} else {
			PBsetCookie('pb_postcode',currentPostCodeOldVal,60);
			unblock( $node );
			return false;
		}

	};


	/**
	 * Object to handle AJAX calls for shipping changes.
	 */
	var pb_shipping_calc = {

		/**
		 * Initialize event handlers and UI state.
		 */
		init: function() {
			this.toggle_shipping            = this.toggle_shipping.bind( this );
			this.shipping_calculator_submit = this.shipping_calculator_submit.bind( this );

			$( document ).on(
				'click',
				'.shipping-calculator-button',
				this.toggle_shipping
			);
			$( document ).on(
				'submit',
				'form.woocommerce-shipping-calculator',
				this.shipping_calculator_submit
			);
			$( document ).on(
				'click',
				'.use_geo_btn',
				this.clear_postcode
			);

			$( '.shipping-calculator-form' ).hide();
		},

		/**
		 * Toggle Shipping Calculator panel
		 */
		toggle_shipping: function() {
			
			var $form = $( '.shipping-calculator-form' );

			block( $form );
			locationCheck( $form );

			$( '.shipping-calculator-form' ).slideToggle( 'slow' );
			$( document.body ).trigger( 'country_to_state_changed' ); // Trigger select2 to load.
			return false;
		},

		clear_postcode: function() {

			var $form = $( '.shipping-calculator-form' );
			
			block( $form );
			locationCheck( $form, true );
		},

		/**
		 * Handles a shipping calculator form submit.
		 *
		 * @param {Object} evt The JQuery event.
		 */
		shipping_calculator_submit: function( evt ) {
			evt.preventDefault();

			var $form = $( evt.currentTarget );

			var postcodeVal = $(evt.currentTarget).find('input[name="calc_shipping_postcode"]');
			var countryVal = $(evt.currentTarget).find('select[name="calc_shipping_country"]');

			block( $form );

			// if country select is default to null, tell user it's required
			if ( $.isEmptyObject(countryVal.val()) ) {
				$(countryVal).after('<p class="porterbuddy-error">'+objectL10n.countryError+'</p>');
				setTimeout(function(){
					$(countryVal).next().remove();
				}, 3000);
				unblock( $form );
				return false;
			}

			// if postcode is less than 3, return inform user
			if ( postcodeVal.val().length < 3 ) {
				$(postcodeVal).after('<p class="porterbuddy-error">'+objectL10n.postcodeError+'</p>');
				setTimeout(function(){
					$(postcodeVal).next().remove();
				}, 3000);
				unblock( $form );
				return false;
			}

			// Provide the submit button value because wc-form-handler expects it.
			$( '<input />' ).attr( 'type', 'hidden' )
							.attr( 'name', 'calc_shipping' )
							.attr( 'value', 'x' )
							.appendTo( $form );

			// Make call to actual form post URL.
			$.ajax( {
				type:     $form.attr( 'method' ),
				url:      $form.attr( 'action' ),
				data:     $form.serialize(),
				dataType: 'html',
				success:  function( response ) {

					PBsetCookie('pb_postcode', postcodeVal.val(), 60);
					PBsetCookie('pb_country', countryVal.val(), 60);
					
					window.location.reload();
					
				},
				complete: function() {
					unblock( $form );
				}
			} );
		}
	};

	// initiate shipping calculator
	pb_shipping_calc.init();

	// if neither geo nor postcode is set
	if ( PBgetCookie('pb_location') == undefined || PBgetCookie('pb_location') == "x")
	{
		// if plugin option is set to find geo on product page load
		ready('.woocommerce-shipping-calculator', function() 
		{
			if ( $('.woocommerce-shipping-calculator').data('geo') === "yes-pl" )
			{
				locationCheck( $('.woocommerce-shipping-calculator'), true );
			}
			
		});
	}
	

} );