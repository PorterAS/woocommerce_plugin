<?php

/**
 * Plugin Name:             PorterBuddy Shipping
 * Plugin URI:
 * Description:             Adds Porterbuddy as a delivery option for your sales
 * Author:                  Ellera AS
 * Author URI:              https://ellera.no/
 *
 * Version:                 0.0.3
 * Requires at least:       4.6
 * Tested up to:
 *
 * WC requires at least:    2.6
 * WC tested up to:
 *
 * Text Domain:             porterbuddy-wc
 * Domain Path:             /languages
 *
 * @package                 WooCommerce
 * @category                Shipping Method
 * @author                  Ellera AS
 */

if(!defined('ABSPATH') || ! defined( 'WPINC' )) {
	die;
}

define( 'PORTERBUDDY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PORTERBUDDY_PLUGIN_NAME', "porterbuddy-wc");

// load language support
load_plugin_textdomain( PORTERBUDDY_PLUGIN_NAME, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

// plugin requires WC plugin
include_once 'includes/requirements.php';

// If WC is not active, do not execute any more code
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Plugin activation
	include_once 'includes/activation.php';

	// load PorterBuddy Class
	require_once 'PorterBuddyClass.php';

	// load shipping method class
	include_once 'includes/shipping_methods.php';

	// Fetch the settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

	// Order Validator
	include_once 'includes/validate_order.php';
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_review_order_before_cart_contents', 'porterbuddy_validate_order' , 10 );
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_after_checkout_validation', 'porterbuddy_validate_order' , 10 );

	// Global actions and filters
	include_once 'includes/hooks.php';

	// CSS and JS includes
	include_once 'includes/scripts.php';

	// Product Widget
	include_once 'parts/porterbuddy-product-widget.php';
	if(
		(isset($settings['enabled']) && $settings['enabled'] == 'yes') &&
		(isset($settings['product_page_widget_enabled']) && $settings['product_page_widget_enabled'] == 'yes')
	) add_action( 'woocommerce_product_meta_end', 'pb_product_display', 5 );

	// Cart Widget
	include_once 'parts/porterbuddy-cart-widget.php';
	if(
		isset($settings['enabled']) && $settings['enabled'] == 'yes'
	) add_action( 'woocommerce_proceed_to_checkout', 'pb_cart_display', 10 );


	// Checkout Widget
	function pb_checkout_display()
	{
		echo "Here";
	}
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes' ) add_action( 'woocommerce_review_order_after_payment', 'pb_checkout_display', 10 );

	// Order Placed Hook
	function pb_order_status_processing( $order_id ) {
		// Send API Request to PB
	}
	add_action( 'woocommerce_order_status_processing', 'pb_order_status_completed', 10, 1 );

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
	add_action( 'woocommerce_admin_order_data_after_shipping_address', 'pb_admin_display', 10, 1 );

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

	// Hide our custom meta data
	function pb_woocommerce_hidden_order_itemmeta($arr) {
		$arr[] = '_pb_window_start';
		$arr[] = '_pb_window_end';
		$arr[] = '_pb_price';
		$arr[] = '_pb_idcheck';
		return $arr;
	}

	add_filter('woocommerce_hidden_order_itemmeta', 'pb_woocommerce_hidden_order_itemmeta', 10, 1);
}