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
add_action('woocommerce_thankyou', 'pb_display_order_complete', 10, 1);
function pb_display_order_complete( $order_id ) {

	if ( ! $order_id ) return;
	$order = wc_get_order( $order_id );

	if( $order->has_shipping_method(PORTERBUDDY_PLUGIN_NAME) ) {
		add_filter( 'woocommerce_get_order_item_totals', 'pb_add_shipping_information', 10, 2 );
		$order->add_meta_data( 'porterbuddy_shipping', 'yes', true);
		$items = $order->get_items('shipping');
		foreach ($items as $item)
		{
			if($item->get_method_id() == PORTERBUDDY_PLUGIN_NAME)
			{
				// Displaying something
				echo '<h2>Porterbuddy Delivery</h2>';
				echo "<p>The order will be delivered between XX.XX and XX.XX on Month. XX</p>";
			}
		}
	}
}
function pb_add_shipping_information( $total_rows, $order )
{
	$total_rows['shipping']['value'] = $total_rows['shipping']['value'].'<br><small>Delivered between XX.XX and XX.XX on Month. XX</small>';
	return $total_rows;
}

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

