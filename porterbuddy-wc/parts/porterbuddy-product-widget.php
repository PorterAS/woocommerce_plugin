<?php

function pb_product_display() {
	global $product;

	if($product->get_stock_status() == 'instock')
	{
		// Fetch the settings
		$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

		echo '<div class="porterbuddy-widget porterbuddy-product">';

		if(
			(
				$settings['geo_widget'] == 'yes' &&
				strlen($settings['mapbox_api_key']) < 5
			) || (
				$settings['mode'] == 'production' &&
				strlen($settings['api_key_prod']) < 5
			) || (
				$settings['mode'] != 'production' &&
				strlen($settings['api_key_testing']) < 5
			)
		){
			echo '<p>Invalid API keys. Update your settings.</p>';
		}
		else
		{
			echo "<p>";

			$postcode = isset($_COOKIE['pb_postcode']) && $_COOKIE['pb_postcode'] == 'x' ? null : (
			WC()->customer->get_shipping_postcode() != null ? WC()->customer->get_shipping_postcode() : (
			isset($_COOKIE['pb_postcode']) ? $_COOKIE['pb_postcode'] : ''
			)
			);

			$country = isset($_COOKIE['pb_country']) && $_COOKIE['pb_country'] == 'x' ? null : (
			WC()->customer->get_shipping_country() != null ? WC()->customer->get_shipping_country() : (
			isset($_COOKIE['pb_country']) ? $_COOKIE['pb_country'] : ''
			)
			);

			if($postcode == null)
			{
				if(isset($_COOKIE["pb_location"]) && isset($settings['mapbox_api_key']) && $settings['mapbox_api_key'] != '')
				{
					$loc = json_decode($_COOKIE['pb_location']);
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, "https://api.mapbox.com/geocoding/v5/mapbox.places/".$loc[1]."%2C".$loc[0].".json?access_token=".$settings['mapbox_api_key']."&types=postcode,country");

					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

					$result = json_decode(curl_exec($ch));

					curl_close($ch);

					if($result != null && isset($result->features[0]->text) && isset($result->features[1]->properties->short_code)) {
						$postcode = $result->features[0]->text;
						$country = strtoupper($result->features[1]->properties->short_code);
						// set WC shipping info
						WC()->customer->set_shipping_postcode( $postcode );
						WC()->customer->set_shipping_country( $country );
						// set post code cookie, so we don't have to check the API every time. Use JS because WP..
						$_COOKIE['pb_postcode'] = $postcode; $_COOKIE['pb_country'] = $country;
						echo '<div style="display:none;">';
						echo '<script type="text/javascript">PBsetCookie(\'pb_postcode\', '.$postcode.', 30);</script>';
						echo '<script type="text/javascript">PBsetCookie(\'pb_country\', "'.$country.'", 30);</script>';
						echo '</div>';
					};

				}
			}
			if($postcode != null && strlen($postcode) > 2)
			{
				global $wp_locale;
				$postcode = str_pad($postcode, 4, "0", STR_PAD_LEFT);
				// Check if the postcode is valid for PB
				global $wpdb;
				$zones = $wpdb->get_results( "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'porterbuddy-wc'", ARRAY_A );
				$ids = [];
				foreach ($zones as $zone) $ids[] = $zone['zone_id'];
				$valid_postcode = $wpdb->get_results( "SELECT location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE 
						zone_id IN (".implode(',',$ids).") AND location_type = 'postcode' AND location_code='".$postcode."'", ARRAY_A );
				$valid_country = $wpdb->get_results( "SELECT location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE 
						zone_id IN (".implode(',',$ids).") AND location_type = 'country' AND location_code='".$country."'", ARRAY_A );
				if(!empty($valid_postcode) && !empty($valid_country))
				{
					$opening_hours = formatClosingHours($settings);
					$prep_time = $settings['available_until']+$settings['packing_time'];
					$now = new DateTime('now', new DateTimeZone('Europe/Oslo'));
					$now_in_a_minute = new DateTime('now', new DateTimeZone('Europe/Oslo'));
					$now_in_a_minute->modify('+1 minutes');
					$today = new \DateTime('now', new DateTimeZone('Europe/Oslo'));
					$closes_today = $opening_hours[$today->format('l')]['close'];
					$today->setTime(substr($closes_today,0,2),substr($closes_today,2,2), '00');
					$today->modify('-'.$prep_time.' minutes');

					if($opening_hours[$today->format('l')]['open'] < $closes_today && $now_in_a_minute < $today) {
						// We can deliver today!
						$interval  = $today->diff( $now );
						$countdown = createCountdown( $interval->d, $interval->h, $interval->i );
						$date      = __('Today', 'porterbuddy-wc');
					}
					else
					{
						$tomorrow = new DateTime('now', new DateTimeZone('Europe/Oslo'));
						$tomorrow->modify('+24 hours');
						$closes_tomorrow = $opening_hours[$tomorrow->format('l')]['close'];
						$tomorrow->setTime(substr($closes_tomorrow,0,2),substr($closes_tomorrow,2,2), '00');

						if((int) $opening_hours[$tomorrow->format('l')]['open'] < (int)$opening_hours[$tomorrow->format('l')]['close'])
						{
							// We can deliver tomorrow!
							$interval  = $tomorrow->diff( $now );
							$countdown = createCountdown( $interval->d, $interval->h, $interval->i );
							$date      = __('Tomorrow', 'porterbuddy-wc');
						}
						else {
							// Can we deliver any other day of the week?
							$next = new DateTime('now', new DateTimeZone('Europe/Oslo'));
							$next->modify('+48 hours');
							$i = 1;
							while((int)$opening_hours[$next->format('l')]['open'] >= (int)$opening_hours[$next->format('l')]['close'] && $i < 6)
							{
								$i++;
								$next->modify('+24 hours');
							}
							if($i < 6)
							{
								$closes_next = $opening_hours[$next->format('l')]['close'];
								$next->setTime(substr($closes_next,0,2),substr($closes_next,2,2), '00');
								$next->modify('-'.$prep_time.' minutes');
								$interval  = $next->diff( $now );
								$countdown = createCountdown( $interval->d, $interval->h, $interval->i );
								$date      = __( $next->format('l'), 'porterbuddy-wc');
							}
						}
					}
					if(isset($date) && isset($countdown))
					{
						echo str_replace(['{{date}}', '{{countdown}}'], [$date, $countdown], __($settings['availability_text'], 'porterbuddy-wc'));
					}
					else echo __($settings['no_available_dates'], 'porterbuddy-wc');
				}
				else
				{
					if(empty($valid_country) && isset(\WC()->countries->countries[$country])) echo str_replace('{{postcode}}', \WC()->countries->countries[$country], __($settings['postcode_unavailable_text'], 'porterbuddy-wc'));
					else echo str_replace('{{postcode}}', $postcode, __($settings['postcode_unavailable_text'], 'porterbuddy-wc'));
				}
			}
			else
			{
				// PostCode is not set
				echo __($settings['click_to_see'], 'porterbuddy-wc');
			}

			echo "</p>";
		}

		// Include shipping calculator to set country and postcode
		include('porterbuddy-shipping-calc.php');

		// close the widget
		echo '</div>';
	}
}

// Generate the product page countdown values
function createCountdown($d, $h, $m)
{
	$string = [];
	if($d > 0) $string[] = $d.' '. __( 'days', 'porterbuddy-wc'  );
	if($h > 0) $string[] = $h.' '. __( 'hours', 'porterbuddy-wc' );
	if($d == 0 && $m > 0) $string[] = $m.' '.__( 'minutes', 'porterbuddy-wc' );
	return implode(' '.__( 'and', 'porterbuddy-wc' ).' ', $string);
}

// Format the opening/closing hours
function formatClosingHours($settings)
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