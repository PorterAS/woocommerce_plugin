<?php

/**
 * Plugin Name:             PorterBuddy Shipping
 * Plugin URI:
 * Description:             Adds Porterbuddy as a delivery option for WooCommerce
 * Author:                  Ellera AS
 * Author URI:              https://ellera.no/
 *
 * Version:                 1.3.0
 * Requires at least:       4.9.8
 * Tested up to:            4.9.8
 *
 * WC requires at least:    3.4.4
 * WC tested up to:         3.4.4
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
	) {
		add_action( 'woocommerce_proceed_to_checkout', 'pb_cart_display', 10 );
	}

	// Checkout Widget
	if(
		isset($settings['enabled']) && $settings['enabled'] == 'yes' 
	) add_action( 'woocommerce_after_order_notes', 'pb_cart_display', 10 );

	include_once 'includes/display_filters.php';
}

/**
 * Hide PorterBuddy when API returns false on shipping request.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function pb_hide_shipping_method_if_not_available( $rates ) {
	
	$api_result = include dirname(__FILE__).'/includes/availability.php';	

	if ( ! isset($api_result["success"]) ) return $rates;
	
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'porterbuddy-wc' === $rate->method_id ) {
			if ($api_result["success"] === false || ! isset($api_result['data']['delivery']) ) {
				unset($rates[ $rate_id ]);
			}
		}
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'pb_hide_shipping_method_if_not_available', 100 );
