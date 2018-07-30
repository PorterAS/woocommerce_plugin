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

