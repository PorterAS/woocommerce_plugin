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
	) {
		add_action( 'woocommerce_proceed_to_checkout', 'pb_cart_display', 10 );
	}

	// Checkout Widget
	if(
		isset($settings['enabled']) && $settings['enabled'] == 'yes' 
	) add_action( 'woocommerce_review_order_before_payment', 'pb_cart_display', 10 );

	include_once 'includes/display_filters.php';
}