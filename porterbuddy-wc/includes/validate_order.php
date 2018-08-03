<?php
function porterbuddy_validate_order( $posted )   {

	$packages = WC()->shipping->get_packages();

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

	if( is_array( $chosen_methods ) && in_array( PORTERBUDDY_PLUGIN_NAME, $chosen_methods ) ) {


		foreach ( $packages as $i => $package ) {

			if ( $chosen_methods[ $i ] != PORTERBUDDY_PLUGIN_NAME ) {
				continue;
			}

			$PorterBuddy_Shipping_Method = new PorterBuddy_Shipping_Method();
			$weightLimit = (int) $PorterBuddy_Shipping_Method->settings['weight'];
			$weight = 0;

			foreach ( $package['contents'] as $item_id => $values )
			{
				$_product = $values['data'];
				$product_weight = $_product->get_weight() == '' ? (int) $PorterBuddy_Shipping_Method->settings['default_product_weight'] : $_product->get_weight();
				$weight = $weight + $product_weight * $values['quantity'];
			}

			$weight = wc_get_weight( $weight, 'kg' );

			if( $weight > $weightLimit ) {

				$message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', PORTERBUDDY_PLUGIN_NAME ), $weight, $weightLimit, $PorterBuddy_Shipping_Method->title );

				$messageType = "error";

				if( ! wc_has_notice( $message, $messageType ) ) {

					wc_add_notice( $message, $messageType );

				}
			}
		}
	}
}