<?php
// Dependencies
include_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-load.php';
include_once dirname(dirname(__FILE__)).'/PorterBuddyClass.php';

// Fetch the settings
$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

if ( false === ( $result = get_transient( 'pb_availability' ) ) || $settings['mode'] == 'development') {
	$cart = WC()->cart->get_cart();

	// Result template
	$result = [
		'success' => false,
		'mode' => $settings['mode'],
		'cached' => false,
		'country' => \WC()->customer->get_shipping_country(),
		'postcode' => \WC()->customer->get_shipping_postcode(),
		'data' => []
	];


	if($settings['mode'] == 'production') $api_key = $settings['api_key_prod'];
	else $api_key =  $settings['api_key_testing'];

	// TODO Implement production URL
	if($settings['mode'] == 'production') $api_url =  'https://api.porterbuddy-test.com/';
	else $api_url =  'https://api.porterbuddy-test.com/';

	$opening_hours = [
			'Monday' => ['open' => $settings['monday_open'], 'close' => $settings['monday_close']],
			'Tuesday' => ['open' => $settings['tuesday_open'], 'close' => $settings['tuesday_close']],
			'Wednesday' => ['open' => $settings['wednesday_open'], 'close' => $settings['wednesday_close']],
			'Thursday' => ['open' => $settings['thursday_open'], 'close' => $settings['thursday_close']],
			'Friday' => ['open' => $settings['friday_open'], 'close' => $settings['friday_close']],
			'Saturday' => ['open' => $settings['saturday_open'], 'close' => $settings['saturday_close']],
			'Sunday' => ['open' => $settings['sunday_open'], 'close' => $settings['sunday_close']],
		];

	if($api_key != '')
	{
		if(isset(\WC()->countries->countries[$result['country']]) && strlen($result['postcode']) > 2 )
		{
			// Postcode and Country is set
			$origin_address = new Address(
				get_option( 'woocommerce_store_address', false ),
				get_option( 'woocommerce_store_address_2', false ),
				get_option( 'woocommerce_store_postcode', false ),
				get_option( 'woocommerce_store_city', false ),
				\WC()->countries->countries[ substr(get_option( 'woocommerce_default_country', 'NO' ), 0, 2) ]
			);
			$destination_address = new Address(
				null,
				null,
				$result['postcode'],
				null,
				\WC()->countries->countries[ $result['country'] ]
			);

			$parcels = [];
			foreach ($cart as $item)
			{
				$product = wc_get_product($item['product_id']);
				$parcel = new Parcel(
					$product->get_width() == '' ? $settings['default_product_width'] : $product->get_width(),
					$product->get_height() == '' ? $settings['default_product_height'] : $product->get_height(),
					$product->get_length() == '' ? $settings['default_product_depth'] : $product->get_length(),
					$product->get_weight() == '' || $product->get_weight() == 0 ? $settings['default_product_weight']*1000 :  wc_get_weight($product->get_weight(),'g'),
					''
				);
				for ($i = 0; $i < $item['quantity']; $i++) {
					$parcels[] = $parcel;
				}

			}

			if(empty($parcels)) $parcels[] = new Parcel(
				1,
				1,
				1,
				1,
				'Dummy Package - Just to get Availability'
			);

			$days = is_numeric($settings['days_ahead']) ? $settings['days_ahead'] : 3;
			$windows = [];

			$prep_time = $settings['available_until']+$settings['packing_time'];
			$now = new DateTime('now', new DateTimeZone('Europe/Oslo'));

			$i = 1;
			$j = 0;
			while($i <= $days && $j < $days*7) {

				$opening = new \DateTime('now', new DateTimeZone('Europe/Oslo'));
				$opening->modify('+'.($j*24).' hours');
				$opening_time = $opening_hours[$opening->format('l')]['open'];
				$opening->setTime(substr($opening_time,0,2),substr($opening_time,2,2), '00');

				$closing = new \DateTime('now', new DateTimeZone('Europe/Oslo'));
				$closing->modify('+'.($j*24).' hours');
				$closing_time = $opening_hours[$closing->format('l')]['close'];
				$closing->setTime(substr($closing_time,0,2),substr($closing_time,2,2), '00');

				$j++;
				if($opening < $closing)
				{
					$i++;
					$closing->modify('-'.$prep_time.' minutes');
					$windows[] = new Window($opening->format('c'), $closing->format('c'));
				}
			}
			$buddy = new Buddy($api_key, $api_url);
			$res = $buddy->checkAvailability(
				$origin_address,
				$destination_address,
				$windows,
				$parcels,
				['delivery', 'express']
			);
			if(isset($res->deliveryWindows))
			{
				$result['locale_code'] = get_locale();
				if(isset($wp_locale)) $result['locale'] = $wp_locale;
				$result['success'] = true;
				$result['data'] = [];
				foreach ($res->deliveryWindows as $win) {
					if($win->product == 'express')
					{
						if($settings['express_price_override'] == "") $cost = $win->price->fractionalDenomination/100;
						else $cost = $settings['express_price_override'];
					}
					else
					{
						if($settings['price_override'] == "") $cost = $win->price->fractionalDenomination/100;
						else $cost = $settings['price_override'];
					}
					$win->price->string = number_format_i18n( $cost, 2 );
					$win->price->return = number_format_i18n( $cost + $settings['return_price'], 2 );
					$result['data'][$win->product][] = $win;
				}
				if($settings['mode'] != 'development') set_transient( 'pb_availability', $result, is_numeric($settings['update_delivery']) ? $settings['update_delivery']*60 : 5*60 );
			}
			else
			{
				if($settings['mode'] == 'production') $result['data'] = ['error' => 'Unknown error'];
				elseif($res) $result['data'] = ['error' => 'API Returned null - no connection?', 'dump' => $res];
				else $result['data'] = $res;
			}
		}
		else $result['data'] = ['error' => 'Postcode and/or country is not valid'];
	}
	else $result['data'] = ['error' => 'API-Key is missing for '.$settings['mode']];
}
else $result['cached'] = true;
return $result;
