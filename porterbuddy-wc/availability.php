<?php
// Dependencies
include_once '../../../wp-load.php';
include_once 'PorterBuddyClass.php';

if ( false === ( $result = get_transient( 'pb_availability' ) ) || $settings['mode'] == 'development') {
	$cart = WC()->cart->get_cart();

	// Fetch the settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

	// Result template
	$result = [
		'success' => false,
		'mode' => $settings['mode'],
		'cached' => false,
		'country' => \WC()->customer->get_shipping_country(),
		'postcode' => \WC()->customer->get_shipping_postcode(),
		'data' => []
	];

	// Functions
	function get_api_key($settings)
	{
		if($settings['mode'] == 'production') return $settings['api_key_prod'];
		else return $settings['api_key_testing'];
	}
	function get_url($settings)
	{
		// TODO Implement production URL
		if($settings['mode'] == 'production') return 'https://api.porterbuddy-test.com/';
		else return 'https://api.porterbuddy-test.com/';
	}
	function get_opening_hours($settings)
	{
		return [
			'Monday' => ['open' => $settings['monday_open'], 'close' => $settings['monday_close']],
			'Tuesday' => ['open' => $settings['tuesday_open'], 'close' => $settings['tuesday_close']],
			'Wednesday' => ['open' => $settings['wednesday_open'], 'close' => $settings['wednesday_close']],
			'Thursday' => ['open' => $settings['thursday_open'], 'close' => $settings['thursday_close']],
			'Friday' => ['open' => $settings['friday_open'], 'close' => $settings['friday_close']],
			'Saturday' => ['open' => $settings['saturday_open'], 'close' => $settings['saturday_close']],
			'Sunday' => ['open' => $settings['sunday_open'], 'close' => $settings['sunday_close']],
		];
	}

	if(get_api_key($settings) != '')
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

			$days = is_numeric($settings['days_ahead']) ? $settings['days_ahead'] : 3;
			$opening_hours = get_opening_hours($settings);
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
			$buddy = new Buddy(get_api_key($settings), get_url($settings));
			$res = $buddy->checkAvailability(
				$origin_address,
				$destination_address,
				$windows,
				$parcels,
				['delivery', 'express']
			);
			if(isset($res->deliveryWindows))
			{
				$result['locale'] = $wp_locale;
				$result['success'] = true;
				$result['data'] = [];
				foreach ($res->deliveryWindows as $win) {
					$win->price->string = number_format_i18n( $win->price->fractionalDenomination/100, 2 );
					$result['data'][$win->product][] = $win;
				}
				if($settings['mode'] != 'development') set_transient( 'pb_availability', $result, is_numeric($settings['update_delivery']) ? $settings['update_delivery']*60 : 5*60 );
			}
			else
			{
				if($settings['mode'] == 'production') $result['data'] = ['error' => 'Unknown error'];
				else $result['data'] = $res;
			}
		}
		else $result['data'] = ['error' => 'Postcode and/or country is not valid'];
	}
	else $result['data'] = ['error' => 'API-Key is missing for '.$settings['mode']];
}
else $result['cached'] = true;

echo json_encode($result);