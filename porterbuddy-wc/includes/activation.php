<?php
if(!defined('ABSPATH') || ! defined( 'WPINC' )) {
	die;
}

function porterbuddy_activate() {
	global $wpdb;
	$zone_name = 'Oslo (PorterBuddy)';
	$available_zones = WC_Shipping_Zones::get_zones();

	$available_zones_names = array();

	foreach ($available_zones as $zone ) {
		if( !in_array( $zone['zone_name'], $available_zones_names ) ) {
			$available_zones_names[] = $zone['zone_name'];
		}
	}
	if( ! in_array( $zone_name, $available_zones_names ) )
	{
		$new_zone = new WC_Shipping_Zone();
		$new_zone->set_zone_name( $zone_name );
		$new_zone->add_location( 'NO', 'country' );

		$file = include_once dirname(__FILE__).'/postal_codes.php';
		$lines = explode("\n", $file);
		foreach ($lines as $line)
		{
			$new_zone->add_location( $line, 'postcode' );
		}

		$instance_id = $new_zone->add_shipping_method(PORTERBUDDY_PLUGIN_NAME);
		$new_zone->save();

		if ( $wpdb->update( "{$wpdb->prefix}woocommerce_shipping_zone_methods", array( 'is_enabled' => false ), array( 'instance_id' => $instance_id ) ) ) {
			do_action( 'woocommerce_shipping_zone_method_status_toggled', $instance_id, PORTERBUDDY_PLUGIN_NAME, $new_zone->get_id(), false );
		}
	}
}
register_activation_hook( dirname(dirname(__FILE__)).'/porterbuddy-wc.php', 'porterbuddy_activate' );