<?php
function porterbuddy_activate() {
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

		$file = include_once 'includes/postal_codes.php';
		$lines = explode("\n", $file);
		foreach ($lines as $line)
		{
			$new_zone->add_location( $line, 'postcode' );
		}

		$new_zone->add_shipping_method(PORTERBUDDY_PLUGIN_NAME);
		$new_zone->save();
	}
}
register_activation_hook( __FILE__, 'porterbuddy_activate' );