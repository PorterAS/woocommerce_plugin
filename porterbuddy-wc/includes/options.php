<?php

if(!defined('ABSPATH') || ! defined( 'WPINC' )) {
	die;
}
$hour_dropdown = array(
	'0000' => '00:00',
	'0015' => '00:15',
	'0030' => '00:30',
	'0045' => '00:45',
	'0100' => '01:00',
	'0115' => '01:15',
	'0130' => '01:30',
	'0145' => '01:45',
	'0200' => '02:00',
	'0215' => '02:15',
	'0230' => '02:30',
	'0245' => '02:45',
	'0300' => '03:00',
	'0315' => '03:15',
	'0330' => '03:30',
	'0345' => '03:45',
	'0400' => '04:00',
	'0415' => '04:15',
	'0430' => '04:30',
	'0445' => '04:45',
	'0500' => '05:00',
	'0515' => '05:15',
	'0530' => '05:30',
	'0545' => '05:45',
	'0600' => '06:00',
	'0615' => '06:15',
	'0630' => '06:30',
	'0645' => '06:45',
	'0700' => '07:00',
	'0715' => '07:15',
	'0730' => '07:30',
	'0745' => '07:45',
	'0800' => '08:00',
	'0815' => '08:15',
	'0830' => '08:30',
	'0845' => '08:45',
	'0900' => '09:00',
	'0915' => '09:15',
	'0930' => '09:30',
	'0945' => '09:45',
	'1000' => '10:00',
	'1015' => '10:15',
	'1030' => '10:30',
	'1045' => '10:45',
	'1100' => '11:00',
	'1115' => '11:15',
	'1130' => '11:30',
	'1145' => '11:45',
	'1200' => '12:00',
	'1215' => '12:15',
	'1230' => '12:30',
	'1245' => '12:45',
	'1300' => '13:00',
	'1315' => '13:15',
	'1330' => '13:30',
	'1345' => '13:45',
	'1400' => '14:00',
	'1415' => '14:15',
	'1430' => '14:30',
	'1445' => '14:45',
	'1500' => '15:00',
	'1515' => '15:15',
	'1530' => '15:30',
	'1545' => '15:45',
	'1600' => '16:00',
	'1615' => '16:15',
	'1630' => '16:30',
	'1645' => '16:45',
	'1700' => '17:00',
	'1715' => '17:15',
	'1730' => '17:30',
	'1745' => '17:45',
	'1800' => '18:00',
	'1815' => '18:15',
	'1830' => '18:30',
	'1845' => '18:45',
	'1900' => '19:00',
	'1915' => '19:15',
	'1930' => '19:30',
	'1945' => '19:45',
	'2000' => '20:00',
	'2015' => '20:15',
	'2030' => '20:30',
	'2045' => '20:45',
	'2100' => '21:00',
	'2115' => '21:15',
	'2130' => '21:30',
	'2145' => '21:45',
	'2200' => '22:00',
	'2215' => '22:15',
	'2230' => '22:30',
	'2245' => '22:45',
	'2300' => '23:00',
	'2315' => '23:15',
	'2330' => '23:30',
	'2345' => '23:45',
);

return array(
	'general_settings' => array(
		'type'        => 'title',
		'title'       => __( 'General Settings', 'porterbuddy-wc' ),
		'description' => __( 'General settings for PorterBuddy Shipping', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'enabled' => array(
		'title' => __( 'Enable', 'porterbuddy-wc' ),
		'type' => 'checkbox',
		'description' => __( 'Enable this shipping method.', 'porterbuddy-wc' ),
		'default' => 'no'
	),

	'title' => array(
		'title' => __( 'Title', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Title to be display on site', 'porterbuddy-wc' ),
		'default' => __( 'Deliver with PorterBuddy', 'porterbuddy-wc' )
	),

	'description' => array(
		'title' => __( 'Description', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Delivery method description', 'porterbuddy-wc' ),
		'default' => __( 'Simple and flexible. You decide when and where delivery will take place, at home, at work or elsewhere.', 'porterbuddy-wc' )
	),

	'express' => array(
		'title' => __( 'Express Delivery Name', 'porterbuddy-wc' ),
		'type' => 'text',
		'default' => __( 'Express shipping', 'porterbuddy-wc' )
	),

	'mode' => array(
		'title' => __( 'PorterBuddy Mode', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Choose operating mode', 'porterbuddy-wc' ),
		'default' => 'testing',
		'options' => array(
			'development' => __( 'Development' ),
			'testing' => __( 'Testing' ),
			'production' => __( 'Production' )
		)
	),

	'sort_order' => array(
		'title' => __( 'Sort / Ordering', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Sort / Ordering Description', 'porterbuddy-wc' ),
		'default' => __( 'Sort / Ordering Default', 'porterbuddy-wc' )
	),

	'debug' => array(
		'title' => __( 'Debugging', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Display debugging information', 'porterbuddy-wc' ),
		'default' => 0,
		'options' => array(
			0 => __( 'No' ),
			1 => __( 'Yes' ),
		)
	),

	'geo_widget' => array(
		'title' => __( 'Show Geo-based availability widget', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => 'always',
		'options' => array(
			'hide' => __( 'Hide' ),
			'available' => __ ( 'Show when available' ),
			'always' => __( 'Always show' )
		)
	),

	'location_discovery' => array(
		'title' => __( 'Location discovery', 'porterbuddy-wc' ),
		'type' => 'multiselect',
		'description' => __( 'Select methods for location discovery', 'porterbuddy-wc' ),
		'default' => array('browser', 'ip'),
		'options' => array(
			'browser' => __( 'Browser location API lookup' ),
			'ip' => __ ( 'IP location lookup' ),
		)
	),

	'location_link' => array(
		'title' => __( 'Click to see text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can use {{location}}, {{postcode}}, {{city}}, {{country}} placeholders', 'porterbuddy-wc' ),
		'default' => __( "Deliver to {{location}} <a class='button'>Change</a>", 'porterbuddy-wc' )
	),


	'product_page_widget' => array(
		'type'        => 'title',
		'title'       => __( 'Product Page Widget', 'porterbuddy-wc' ),
		'description' => __( 'Settings for the PorterBuddy widget on the Product Page', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'product_page_widget_enabled' => array(
		'title' => __( 'Enable Widget', 'porterbuddy-wc' ),
		'type' => 'checkbox',
		'description' => __( 'Enable or disable the product page widget.', 'porterbuddy-wc' ),
		'default' => 'yes'
	),

	'click_to_see' => array(
		'title' => __( 'Click to see text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Text to show on product page when postcode is not set', 'porterbuddy-wc' ),
		'default' => __( 'Add a postcode to see if you are eligible for same day delivery', 'porterbuddy-wc' )
	),

	'availability_text' => array(
		'title' => __( 'Availability text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can use {{date}} and {{countdown}} placeholders', 'porterbuddy-wc' ),
		'default' => __( 'Want it {{date}}? Order in the next {{countdown}}', 'porterbuddy-wc' )
	),

	'postcode_unavailable_text' => array(
		'title' => __( 'Postcode not available text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can use {{location}}, {{postcode}}, {{city}}, {{country}} placeholders', 'porterbuddy-wc' ),
		'default' => __( 'Sorry, same day delivery is not currently available to {{postcode}}', 'porterbuddy-wc' )
	),

	'messages' => array(
		'type'        => 'title',
		'title'       => __( 'Messages', 'porterbuddy-wc' ),
		'description' => __( 'Modify the messages PorterBuddy is displaying to your customers', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'out_of_stock_text' => array(
		'title' => __( 'Product is out of stock text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can use {{location}}, {{postcode}}, {{city}}, {{country}} placeholders', 'porterbuddy-wc' ),
		'default' => __( 'Sorry, product is out of stock', 'porterbuddy-wc' )
	),

	'no_available_dates' => array(
		'title' => __( 'No available dates text', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can use {{location}}, {{postcode}}, {{city}}, {{country}} placeholders', 'porterbuddy-wc' ),
		'default' => __( 'Sorry, same day delivery is not currently available', 'porterbuddy-wc' )
	),

	'popup_title' => array(
		'title' => __( 'Popup Title', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Title on the PorterBuddy widget popup', 'porterbuddy-wc' ),
		'default' => __( 'Choose your delivery location', 'porterbuddy-wc' )
	),

	'popup_description' => array(
		'title' => __( 'Popup Description', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Description on the PorterBuddy widget popup', 'porterbuddy-wc' ),
		'default' => __( 'Delivery options and delivery speeds may vary for different locations', 'porterbuddy-wc' )
	),

	'position' => array(
		'title' => __( 'Widget position', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Choose where the PorterBuddy widget should appear', 'porterbuddy-wc' ),
		'default' => 'checkout',
		'options' => array(
			'checkout' => __( 'Checkout Page' ),
			'confirmation' => __ ( 'Confirmation Page' ),
		)
	),

	'days_ahead' => array(
		'title' => __( 'Available days ahead', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'How many days ahead should be available', 'porterbuddy-wc' ),
		'default' => __( 3, 'porterbuddy-wc' )
	),

	'return' => array(
		'title' => __( 'Return available', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Should the option for return be available', 'porterbuddy-wc' ),
		'default' => 1,
		'options' => array(
			0 => __( 'No' ),
			1 => __( 'Yes' ),
		)
	),

	'return_text' => array(
		'title' => __( 'Text for return', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Text shown for the return option', 'porterbuddy-wc' ),
		'default' => __( 'I would like to return items that do not match me', 'porterbuddy-wc' )
	),

	'return_text_short' => array(
		'title' => __( 'Short text for return', 'porterbuddy-wc' ),
		'type' => 'text',
		'default' => __( '+ included return', 'porterbuddy-wc' )
	),

	'return_price' => array(
		'title' => __( 'Return price', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Price for the return service', 'porterbuddy-wc' ),
		'default' => __( 79, 'porterbuddy-wc' )
	),

	'update_delivery' => array(
		'title' => __( 'Update delivery alternative', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Given in minutes', 'porterbuddy-wc' ),
		'default' => __( 5, 'porterbuddy-wc' )
	),

	'leave_at_door' => array(
		'title' => __( 'Text for leave at the door option', 'porterbuddy-wc' ),
		'type' => 'text',
		'default' => __( 'Allow the courier to leave the package outside the door', 'porterbuddy-wc' )
	),

	'courier_message' => array(
		'title' => __( 'To the messenger box', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Text above the message to courier box'),
		'default' => __( 'Optional message to the courier', 'porterbuddy-wc' )
	),

	'price_override' => array(
		'title' => __( 'Override porterbuddy price with', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Leave empty to use Porterbuddy pricing'),
		'default' => __( '', 'porterbuddy-wc' )
	),

	'express_price_override' => array(
		'title' => __( 'Override porterbuddy express price with', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Leave empty to use Porterbuddy Express pricing'),
		'default' => __( '', 'porterbuddy-wc' )
	),


	'delivery_discount' => array(
		'title' => __( 'Delivery discount', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Not entirely sure how this works'),
		'default' => 'none',
		'options' => array(
			'none' => __( 'None' ),
			'fixed' => __( 'Fixed' ),
			'percentage' => __( 'Percentage' ),
		)
	),

	'opening_hours' => array(
		'type'        => 'title',
		'title'       => __( 'Opening Hours', 'porterbuddy-wc' ),
		'description' => __( 'Opening hours and time settings. If you set close equal to or less then open, that day will be considered closed.', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'available_until' => array(
		'title' => __( 'PorterBuddy available until', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Latest pickup before closing, in minutes'),
		'default' => __( 270, 'porterbuddy-wc' ),
	),

	'monday_open' => array(
		'title' => __( 'Opening monday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0900', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'monday_close' => array(
		'title' => __( 'Closing monday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1800', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'tuesday_open' => array(
		'title' => __( 'Opening tuesday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0900', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'tuesday_close' => array(
		'title' => __( 'Closing tuesday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1800', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'wednesday_open' => array(
		'title' => __( 'Opening wednesday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0900', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'wednesday_close' => array(
		'title' => __( 'Closing wednesday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1800', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'thursday_open' => array(
		'title' => __( 'Opening thursday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0900', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'thursday_close' => array(
		'title' => __( 'Closing thursday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1800', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'friday_open' => array(
		'title' => __( 'Opening friday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0900', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'friday_close' => array(
		'title' => __( 'Closing friday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1800', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'saturday_open' => array(
		'title' => __( 'Opening saturday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1000', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'saturday_close' => array(
		'title' => __( 'Closing saturday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '1500', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'sunday_open' => array(
		'title' => __( 'Opening saturday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0000', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'sunday_close' => array(
		'title' => __( 'Closing saturday', 'porterbuddy-wc' ),
		'type' => 'select',
		'default' => __( '0000', 'porterbuddy-wc' ),
		'options' => $hour_dropdown
	),

	'automatic_shipping' => array(
		'title' => __( 'Add shipping automatically', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Not sure what this does yet' ),
		'default' => 0,
		'options' => array(
			0 => __( 'No' ),
			1 => __ ( 'Yes' )
		)
	),

	'default_phone_country_code' => array(
		'title' => __( 'Default phone country code', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => 'Default phone country code (Ex. "+47")',
		'default' => __( '+47', 'porterbuddy-wc' ),
	),

	'packing_time' => array(
		'title' => __( 'Packing time', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => 'In minutes',
		'default' => __( 15, 'porterbuddy-wc' ),
	),

	'verifications' => array(
		'type'        => 'title',
		'title'       => __( 'Delivery Verifications', 'porterbuddy-wc' ),
		'description' => __( 'Set delivery verifications', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'min_age' => array(
		'title' => __( 'Minimum age check', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Optional', 'porterbuddy-wc' ),
	),

	'signature_required' => array(
		'title' => __( 'Signature on delivery', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Require signature on delivery' ),
		'default' => 0,
		'options' => array(
			0 => __( 'No' ),
			1 => __ ( 'Yes' )
		)
	),

	'id_verification' => array(
		'title' => __( 'Verify ID on delivery', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Courier will check the receivers ID' ),
		'default' => 0,
		'options' => array(
			0 => __( 'No' ),
			1 => __ ( 'Yes' )
		)
	),

	'only_to_recipient' => array(
		'title' => __( 'Only deliver to recipient', 'porterbuddy-wc' ),
		'type' => 'select',
		'description' => __( 'Courier will only deliver to the registered recipient' ),
		'default' => 0,
		'options' => array(
			0 => __( 'No' ),
			1 => __ ( 'Yes' )
		)
	),

	/*
	 * TODO: Pr. product verification
	 */

	'default_product_settings' => array(
		'type'        => 'title',
		'title'       => __( 'Default Product', 'porterbuddy-wc' ),
		'description' => __( 'Default package specifications', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'default_product_weight' => array(
		'title' => __( 'Default product weight', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'In kg, will be used for products without weight' ),
		'default' => __( 0.5, 'porterbuddy-wc' )
	),

	'weight' => array(
		'title' => __( 'Max product weight', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'In kg, max weight for shipments with PorterBuddy' ),
		'default' => __( 50, 'porterbuddy-wc' )
	),

	/*
	 * TODO: Figure out the attribute setting
	 */

	'default_product_height' => array(
		'title' => __( 'Default product height', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'In cm, will be used for products without height' ),
		'default' => __( 20, 'porterbuddy-wc' )
	),

	'default_product_width' => array(
		'title' => __( 'Default product width', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'In cm, will be used for products without width' ),
		'default' => __( 20, 'porterbuddy-wc' )
	),

	'default_product_depth' => array(
		'title' => __( 'Default product depth', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'In cm, will be used for products without depth' ),
		'default' => __( 20, 'porterbuddy-wc' )
	),

	'api_settings' => array(
		'type'        => 'title',
		'title'       => __( 'API Settings', 'porterbuddy-wc' ),
		'description' => __( 'API Settings and keys', 'porterbuddy-wc' ),
		'class'       => 'separated_title_tab',
	),

	'api_key_prod' => array(
		'title' => __( 'API Key (Production)', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Must be set before mode is set to production', 'porterbuddy-wc' ),
		'default' => __( '', 'porterbuddy-wc' )
	),

	'api_key_testing' => array(
		'title' => __( 'API Key (Test/Dev)', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'API Key for testing', 'porterbuddy-wc' ),
		'default' => __( '', 'porterbuddy-wc' )
	),

	'google_maps_api_key' => array(
		'title' => __( 'Google Maps API key', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can be obtained from _LINK_' ),
		'default' => __( '', 'porterbuddy-wc' )
	),

	'mapbox_api_key' => array(
		'title' => __( 'MapBox API key', 'porterbuddy-wc' ),
		'type' => 'text',
		'description' => __( 'Can be obtained from _LINK_' ),
		'default' => __( '', 'porterbuddy-wc' )
	),
);