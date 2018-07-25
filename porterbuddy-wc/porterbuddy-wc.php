<?php

if(!defined('ABSPATH') || ! defined( 'WPINC' )) {
	die;
}
define( 'PORTERBUDDY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

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

define( 'PORTERBUDDY_PLUGIN_NAME', "porterbuddy-wc");

require_once 'PorterBuddyClass.php';

load_plugin_textdomain( 'porterbuddy-wc', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

// plugin requires WC plugin
include_once 'includes/requirements.php';

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	include_once 'includes/shipping_method_class.php';

	// Fetch the settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

	// Order Validator
	include_once 'includes/validate_order.php';
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_review_order_before_cart_contents', 'porterbuddy_validate_order' , 10 );
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_after_checkout_validation', 'porterbuddy_validate_order' , 10 );

	// Plugin activation
	include_once 'includes/activation.php';

	// Product Widget
	include_once 'parts/porterbuddy-product-widget.php';
	if(
		(isset($settings['enabled']) && $settings['enabled'] == 'yes') &&
		(isset($settings['product_page_widget_enabled']) && $settings['product_page_widget_enabled'] == 'yes')
	) add_action( 'woocommerce_product_meta_end', 'pb_product_display', 5 );

	// if PorterBuddy shipping is enabled, display the widget
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_proceed_to_checkout', 'pb_cart_display', 10 );
	function pb_cart_display() 
	{
		// if PorterBuddy shipping is selected, display widget
		if ( WC()->session->get('chosen_shipping_methods')[0] == PORTERBUDDY_PLUGIN_NAME )
		{
			include('parts/porterbuddy-cart-widget.php');
		}
	}

	// Global actions and filters
	include_once 'includes/hooks.php';

	// CSS and JS includes
	include_once 'includes/scripts.php';
}