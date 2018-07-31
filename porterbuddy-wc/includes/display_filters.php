<?php
// Before order is placed.
/**
 * @param $order WC_Order
 * @param $data array
 */
function pb_before_checkout_create_order( $order, $data ) {
	if( $order->has_shipping_method(PORTERBUDDY_PLUGIN_NAME) ) {
		$order->add_meta_data( 'porterbuddy_shipping', 'yes', true);
		$items = $order->get_items('shipping');
		foreach ($items as $item)
		{
			if($item->get_method_id() == PORTERBUDDY_PLUGIN_NAME)
			{
				$item->add_meta_data('_pb_window_start', 'ts', true);
				$item->add_meta_data('_pb_window_end', 'tf', true);
				$item->add_meta_data('_pb_price', 'tf', true);
				$item->add_meta_data('_pb_idcheck', 'tf', true);
				$item->add_meta_data('Will be picked up', 'Between XX.XX and XX.XX on Month. XX', true);
			}
		}
	}
	$order->save();
}
add_action('woocommerce_checkout_create_order', 'pb_before_checkout_create_order', 20, 2);

// Hide our custom meta data (Related to the data above)
function pb_woocommerce_hidden_order_itemmeta($arr) {
	$arr[] = '_pb_window_start';
	$arr[] = '_pb_window_end';
	$arr[] = '_pb_price';
	$arr[] = '_pb_idcheck';
	return $arr;
}
add_filter('woocommerce_hidden_order_itemmeta', 'pb_woocommerce_hidden_order_itemmeta', 10, 1);

// Checkout: Order Complete
function pb_add_shipping_information( $total_rows, $order )
{
	$total_rows['shipping']['value'] = $total_rows['shipping']['value'].'<br><small>Delivered between XX.XX and XX.XX on Month. XX</small>';
	return $total_rows;
}
function pb_display_order_complete( $order_id ) {

	if ( ! $order_id ) return;
	$order = wc_get_order( $order_id );

	if( $order->has_shipping_method(PORTERBUDDY_PLUGIN_NAME) ) {

		add_filter( 'woocommerce_get_order_item_totals', 'pb_add_shipping_information', 10, 2 );
		$items = $order->get_items('shipping');
		foreach ($items as $item)
		{
			if($item->get_method_id() == PORTERBUDDY_PLUGIN_NAME)
			{
				// IF NOT meta key order ID exist: Send API Request to PB
				$pb_order_id = wc_get_order_item_meta($item->get_id(), '_pb_order_id', true);
				if(!$pb_order_id || $pb_order_id == '')
				{
					// Fetch the settings
					$settings = get_option( 'woocommerce_porterbuddy-wc_settings');
					include_once dirname(dirname(__FILE__)).'/PorterBuddyClass.php';

					if($settings['mode'] == 'production') $api_key = $settings['api_key_prod'];
					else $api_key =  $settings['api_key_testing'];

					// TODO Implement production URL
					if($settings['mode'] == 'production') $api_url =  'https://api.porterbuddy-test.com/';
					else $api_url =  'https://api.porterbuddy-test.com/';

					// Call PB
					if($api_key != '') {
						$buddy = new Buddy( $api_key, $api_url );

						// Sanitizing the values
						$window_start = WC()->session->get('pb_windowStart');
						$return_on_demand = WC()->session->get('pb_returnOnDemand') == 'true';
						$type = WC()->session->get('pb_type') == 'express' ? 'express' : 'delivery';
						$leaveDoorStep = WC()->session->get('pb_leaveDoorStep') == 'true';
						$message = WC()->session->get('pb_message');

						if(isset($window_start) && strlen($window_start) > 6)
						{
							$window = null;
							$api_result = include dirname(__FILE__).'/availability.php';
							if(isset($api_result['data'][$type]))
							{
								foreach ($api_result['data'][$type] as $win) {
									if ($win->start == $window_start) $window = Window::load($win);
									break;
								}
							}
						}

						if($window != null) {
							if ( $return_on_demand ) {
								null;
							} // Do something here

							$shipping_address = $order->get_address( 'shipping' );

							$originAddress = new Address(
								get_option( 'woocommerce_store_address', false ),
								get_option( 'woocommerce_store_address_2', false ),
								get_option( 'woocommerce_store_postcode', false ),
								get_option( 'woocommerce_store_city', false ),
								\WC()->countries->countries[ substr( get_option( 'woocommerce_default_country', 'NO' ), 0, 2 ) ]
							);

							$destination_address = new Address(
								$shipping_address['address_1'],
								$shipping_address['address_2'],
								$shipping_address['postcode'],
								$shipping_address['city'],
								\WC()->countries->countries[ $shipping_address['country'] ]
							);

							$origin = new Origin(
								$settings['store_name'],
								$originAddress,
								$settings['store_email'],
								$settings['default_phone_country_code'],
								$settings['store_phone'],
								[$window]
							);

							$destination = new Destination(
								$shipping_address['first_name'] . ' ' . $shipping_address['last_name'],
								$destination_address,
								$order->get_billing_email(),
								$settings['default_phone_country_code'],
								$order->get_billing_phone(),
								$window,
								[
									'minimumAgeCheck'  => $settings['min_age'],
									'leaveAtDoorstep'  => $leaveDoorStep,
									'idCheck'          => $settings['id_verification'] == 1,
									'requireSignature' => $settings['signature_required'] == 1,
									'onlyToRecipient'  => $settings['only_to_recipient'] == 1
								]
							);

							$parcels = [];

							foreach ($order->get_items() as $pitem)
							{
								$product = wc_get_product($pitem['product_id']);
								$parcel = new Parcel(
									$product->get_width() == '' ? $settings['default_product_width'] : $product->get_width(),
									$product->get_height() == '' ? $settings['default_product_height'] : $product->get_height(),
									$product->get_length() == '' ? $settings['default_product_depth'] : $product->get_length(),
									$product->get_weight() == '' || $product->get_weight() == 0 ? $settings['default_product_weight']*1000 :  wc_get_weight($product->get_weight(),'g'),
									$product->get_description() == '' ? 'No decription available' : $product->get_description()
								);
								for ($i = 0; $i < $pitem['quantity']; $i++) {
									$parcels[] = $parcel;
								}

							}

							$result = $buddy->placeOrder(
								$origin,
								$destination,
								$parcels,
								$type,
								$message
							);
							if(isset($result->orderId)) wc_add_order_item_meta( $item->get_id(), '_pb_order_id', $result->orderId, true );
							else echo "Poorterbuddy order failed";
							if(isset($result->deliveryReference)) wc_add_order_item_meta( $item->get_id(), '_pb_delivery_reference', $result->deliveryReference, true );
							if(isset($result->overviewUrl)) wc_add_order_item_meta( $item->get_id(), '_pb_overview_url', $result->overviewUrl, true );

						}
					}
					else die('API-Key missing for '.$settings['mode']);
				}

				// Displaying something
				echo '<h2>Porterbuddy Delivery</h2>';
				echo "<p>The order will be delivered between XX.XX and XX.XX on Month. XX</p>";
			}
		}
	}
}
add_action('woocommerce_thankyou', 'pb_display_order_complete', 10, 1);

// Admin: order
function pb_admin_display($order){
	if( $order->has_shipping_method(PORTERBUDDY_PLUGIN_NAME) ) {
		$order->add_meta_data( 'porterbuddy_shipping', 'yes', true);
		$items = $order->get_items('shipping');
		foreach ($items as $item)
		{
			if($item->get_method_id() == PORTERBUDDY_PLUGIN_NAME)
			{
				// Displaying something
				echo '<h3>Porterbuddy Details</h3>';
				echo "<p>The order will be picked up by a courier between XX.XX and XX.XX on Month XXth</p>";
			}
		}
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'pb_admin_display', 10, 1 );

