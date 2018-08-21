<?php 

/**
 * Global actions and filters to be included.
 */

/**
 * check if postcode/country already exists when creating woo sessions
 */
add_action( 'woocommerce_add_cart_item_data', function() 
{
	// shipping post number
	if ( WC()->customer->get_shipping_postcode() == null )
	{
		if ( isset($_COOKIE['pb_postcode']) && ( $_COOKIE['pb_postcode'] != "x" && $_COOKIE['pb_postcode'] != null ) )
		{
			if(WC()->customer->get_billing_postcode() == null) WC()->customer->set_billing_postcode( $_COOKIE['pb_postcode'] );
			WC()->customer->set_shipping_postcode( $_COOKIE['pb_postcode'] );
		}
	}
	// shipping country
	if ( WC()->customer->get_shipping_country() == null )
	{
		if ( isset($_COOKIE['pb_country']) && ( $_COOKIE['pb_country'] != "x" && $_COOKIE['pb_country'] != null ) )
		{
			if(WC()->customer->get_billing_country() == null) WC()->customer->set_billing_country( $_COOKIE['pb_country'] );
			WC()->customer->set_shipping_country( $_COOKIE['pb_country'] );
		}
	}
}, 50);


/**
 * Add AJAX endpoint to save shipping data
 */
add_action( 'wp_ajax_setShippingSelection', 'setShippingSelection' );
add_action( 'wp_ajax_nopriv_setShippingSelection', 'setShippingSelection' );
function setShippingSelection()
{
	// validate nonce and sanitize input
	if ( 
		isset( $_POST['pb_nonce'] ) &&
		wp_verify_nonce($_POST['pb_nonce'], 'porterbuddy_widget_options') 
	) {
		WC()->session->set( 'pb_type' , sanitize_text_field($_POST['pb_type']) );
		WC()->session->set( 'pb_windowStart' , sanitize_text_field($_POST['pb_windowStart']) );
		WC()->session->set( 'pb_returnOnDemand' , sanitize_text_field($_POST['pb_returnOnDemand']) );
		WC()->session->set( 'pb_leaveDoorStep' , sanitize_text_field($_POST['pb_leaveDoorStep']) );
		WC()->session->set( 'pb_message' , sanitize_text_field($_POST['pb_message']) );

		return true;
	}
	else
	{
		echo json_encode( "Nonce could not be validated!" );
	}
	
	// required to terminate immediately after returning a proper response
	wp_die(); 
}


/**
 * Add AJAX endpoint to get shipping data
 */
add_action( 'wp_ajax_getShippingSelection', 'getShippingSelection' );
add_action( 'wp_ajax_nopriv_getShippingSelection', 'getShippingSelection' );
function getShippingSelection()
{
	// get woo session variables
	$PBsessionData['pb_type'] = WC()->session->get( 'pb_type' );
	$PBsessionData['pb_windowStart'] = WC()->session->get( 'pb_windowStart' );
	$PBsessionData['pb_returnOnDemand'] = WC()->session->get( 'pb_returnOnDemand' );
	$PBsessionData['pb_leaveDoorStep'] = WC()->session->get( 'pb_leaveDoorStep' );
	$PBsessionData['pb_message'] = WC()->session->get( 'pb_message' );

	// check if timewindow exists
	if ( ! empty($PBsessionData['pb_windowStart']) )
	{
		$PBsessionData['status'] = "success";
	}
	else
	{
		$PBsessionData['status'] = "Data was not accessible!";
	}

	// return json data type
	header('Content-Type: application/json');
	echo json_encode( $PBsessionData );
	
	// required to terminate immediately after returning a proper response
	wp_die(); 
}

/**
 * Woocommerce Shipping Cost Cache Killer
 */

// Add to admin-ajax.php for checkout calls
add_action( 'wp_ajax_pb_kill_shipping_cost_cache', 'pb_kill_shipping_cost_cache' );
add_action( 'wp_ajax_nopriv_pb_kill_shipping_cost_cache', 'pb_kill_shipping_cost_cache' );
function pb_kill_shipping_cost_cache()
{
	if(WC()->session->get('chosen_shipping_methods')[0] == PORTERBUDDY_PLUGIN_NAME)
	{
		// START    Shipping cost cache killer
		$contents = WC()->cart->cart_contents;
		foreach ( $contents as $key => $content ) {
			$contents[ $key ]['data_hash'] = md5( time() ); // Unset the hash to force cart update
		}
		WC()->cart->set_cart_contents( $contents );
		WC()->cart->calculate_shipping();
		// END      Shipping cost cache killer
	}
}

// Add to cart and checkout
if(
	isset($settings['enabled']) && $settings['enabled'] == 'yes'
) {
	add_action( 'woocommerce_checkout_before_order_review', 'pb_kill_shipping_cost_cache', 10 );
	add_action( 'woocommerce_cart_totals_before_shipping', 'pb_kill_shipping_cost_cache', 10 );
}

// Pad the postcode if it is shorter than 4 digits
add_filter( 'woocommerce_format_postcode', 'pb_format_postcode', 10, 2 );
function pb_format_postcode( $postcode, $country ){
	$postcode = wc_normalize_postcode( $postcode );
	if($country == 'NO') $postcode = str_pad($postcode, 4, "0", STR_PAD_LEFT);
	return $postcode;
}
