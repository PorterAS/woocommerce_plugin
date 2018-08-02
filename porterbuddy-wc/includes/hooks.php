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
 * Add AJAX endpoints to save shipping data
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
