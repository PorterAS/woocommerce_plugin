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
if ( ! function_exists('isWCActive') )
{

	function isWCActive(){

		// Require parent plugin
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
			// Stop activation redirect and show error
			wp_die('Sorry, but this plugin requires WooCommerce to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '" title="Return to Plugins">&laquo; Return to Plugins</a>');
		}
	}

}
register_activation_hook( __FILE__, 'isWCActive' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function porterbuddy_shipping_method() {
		if ( ! class_exists( 'PorterBuddy_Shipping_Method' ) ) {

			class PorterBuddy_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct( $instance_id = 0 ) {
					$this->id                 = PORTERBUDDY_PLUGIN_NAME;
					$this->method_title       = __( 'PorterBuddy Shipping', $this->id );
					$this->method_description = __( 'Let your customer get their delivery simple and flexible. They decide when and where delivery will take place, at home, at work or elsewhere.', $this->id );
					$this->instance_id        = absint( $instance_id );
					$this->supports = array(
						'shipping-zones',
						'instance-settings',
						'settings'
						);


					$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'PorterBuddy Shipping', $this->id );
					$this->init();
					$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
				}

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API
					$this->init_form_fields();
					$this->init_settings();
					$this->instance_form_fields = array(
						'title'      => array(
							'title'       => __( 'Method title', $this->id ),
							'type'        => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', $this->id ),
							'default'     => __( 'PorterBuddy Shipping', $this->id ),
							'desc_tip'    => true,
						),
						'cost'       => array(
							'title'             => __( 'Cost', $this->id ),
							'type'              => 'text',
							'placeholder'       => '',
							'description'       => 'Default cost before address is checked.',
							'default'           => '50',
							'desc_tip'          => true,
							'sanitize_callback' => array( $this, 'sanitize_cost' ),
						),
					);
					$this->title                = $this->get_option( 'title' );
					$this->cost                 = $this->get_option( 'cost' );
					$this->init_instance_settings();

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				/**
				 * Define settings field for this shipping
				 * @return void
				 */
				function init_form_fields() {

					$this->form_fields = include 'inc/options.php';

				}

				private function get_api_key()
				{
					if($this->get_option('mode') == 'production') return $this->get_option('api_key_prod');
					else return $this->get_option('api_key_testing');
				}

				// Format the opening/closing hours
				public function formatClosingHours()
				{
					return [
						'Monday' => ['open' => $this->get_option('monday_open'), 'close' => $this->get_option('monday_close')],
						'Tuesday' => ['open' => $this->get_option('tuesday_open'), 'close' => $this->get_option('tuesday_close')],
						'Wednesday' => ['open' => $this->get_option('wednesday_open'), 'close' => $this->get_option('wednesday_close')],
						'Thursday' => ['open' => $this->get_option('thursday_open'), 'close' => $this->get_option('thursday_close')],
						'Friday' => ['open' => $this->get_option('friday_open'), 'close' => $this->get_option('friday_close')],
						'Saturday' => ['open' => $this->get_option('saturday_open'), 'close' => $this->get_option('saturday_close')],
						'Sunday' => ['open' => $this->get_option('sunday_open'), 'close' => $this->get_option('sunday_close')],
					];
				}

				/**
				 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package  = array() ) {

					$weight = 0;
					$cost = $this->cost;

					$weight = wc_get_weight( $weight, 'kg' );

					$buddy = new Buddy($this->get_api_key());

					$origin_address = new Address(
						get_option( 'woocommerce_store_address', false ),
						get_option( 'woocommerce_store_address_2', false ),
						get_option( 'woocommerce_store_postcode', false ),
						get_option( 'woocommerce_store_city', false ),
						\WC()->countries->countries[ substr(get_option( 'woocommerce_default_country', 'NO' ), 0, 2) ]
					);

					$destination_address = new Address(
						$package[ 'destination' ][ 'address' ],
						$package[ 'destination' ][ 'address_2' ],
						$package[ 'destination' ][ 'postcode' ],
						$package[ 'destination' ][ 'city' ],
						\WC()->countries->countries[ $package[ 'destination' ][ 'country' ] ]
					);

					$days = is_numeric($this->get_option('days_ahead')) ? $this->get_option('days_ahead') : 3;
					$opening_hours = $this->formatClosingHours();
					$windows = [];

					$prep_time = $this->get_option('available_until')+$this->get_option('packing_time');
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
					$parcels = [];
					foreach ($package['contents'] as $pack)
					{
						$product = $pack['data'];
						$parcels[] = new Parcel(
							$product->get_width() == '' ? $this->settings['default_product_width'] : $product->get_width(),
							$product->get_height() == '' ? $this->settings['default_product_height'] : $product->get_height(),
							$product->get_length() == '' ? $this->settings['default_product_depth'] : $product->get_length(),
							$product->has_weight() ? wc_get_weight($product->get_weight(), 'g') : wc_get_weight($this->settings['default_product_weight'], 'g')
						);
					}

					$delivery_windows = $buddy->checkAvailability(
						$origin_address,
						$destination_address,
						$windows,
						$parcels,
						['delivery', 'express']
					);

					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $cost
					);

					$this->add_rate( $rate );
				}

				/**
				 * Sanitize the cost field.
				 *
				 * @since 0.0.1
				 * @param string $value Unsanitized value.
				 * @return string
				 */
				public function sanitize_cost( $value ) {
					$value = is_null( $value ) ? '' : $value;
					$value = wp_kses_post( trim( wp_unslash( $value ) ) );
					$value = str_replace( array( get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ) ), '', $value );
					return $value;
				}
			}
		}
	}

	// Initialize the shipping method

	add_action( 'woocommerce_shipping_init', 'porterbuddy_shipping_method' );
	function add_porterbuddy_shipping_method( $methods ) {
		$methods[PORTERBUDDY_PLUGIN_NAME] = 'PorterBuddy_Shipping_Method';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_porterbuddy_shipping_method' );


	// Fetch the settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

	function porterbuddy_validate_order( $posted )   {

		$packages = WC()->shipping->get_packages();

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		if( is_array( $chosen_methods ) && in_array( PORTERBUDDY_PLUGIN_NAME, $chosen_methods ) ) {

			foreach ( $packages as $i => $package ) {

				if ( $chosen_methods[ $i ] != PORTERBUDDY_PLUGIN_NAME ) {
					continue;
				}

				$PorterBuddy_Shipping_Method = new PorterBuddy_Shipping_Method();
				$weightLimit = (int) $PorterBuddy_Shipping_Method->settings['weight'];
				$weight = 0;

				foreach ( $package['contents'] as $item_id => $values )
				{
					$_product = $values['data'];
					$weight = $weight + $_product->get_weight() * $values['quantity'];
				}

				$weight = wc_get_weight( $weight, 'kg' );

				if( $weight > $weightLimit ) {

					$message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', PORTERBUDDY_PLUGIN_NAME ), $weight, $weightLimit, $PorterBuddy_Shipping_Method->title );

					$messageType = "error";

					if( ! wc_has_notice( $message, $messageType ) ) {

						wc_add_notice( $message, $messageType );

					}
				}
			}
		}
	}

	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_review_order_before_cart_contents', 'porterbuddy_validate_order' , 10 );
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_after_checkout_validation', 'porterbuddy_validate_order' , 10 );

	// Plugin activation
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

			$file = include_once 'inc/postal_codes.php';
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

	/*
	 * TEST
	 */

	if(
		(isset($settings['enabled']) && $settings['enabled'] == 'yes') &&
		(isset($settings['product_page_widget_enabled']) && $settings['product_page_widget_enabled'] == 'yes')
	) add_action( 'woocommerce_product_meta_end', 'pb_product_display', 5 );

	function pb_product_display() {

		// Fetch the settings
		$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

		echo '<div style="border: 1px solid #CCC; padding: 10px; margin-top: 10px;"><p>';

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
					echo '<script type="text/javascript">PBsetCookie(\'pb_postcode\', '.$postcode.', 30);</script>';
					echo '<script type="text/javascript">PBsetCookie(\'pb_country\', "'.$country.'", 30);</script>';
				};

			}
		}
		if($postcode != null && strlen($postcode) > 2)
		{

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

				$today = new \DateTime('now', new DateTimeZone('Europe/Oslo'));
				$closes_today = $opening_hours[$today->format('l')]['close'];
				$today->setTime(substr($closes_today,0,2),substr($closes_today,2,2), '00');
				$today->modify('-'.$prep_time.' minutes');

				if($opening_hours[$today->format('l')]['open'] < $closes_today && $now < $today) {
					// We can deliver today!
					$interval  = $today->diff( $now );
					$countdown = createCountdown( $interval->d, $interval->h, $interval->i );
					$date      = 'Today';
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
						$date      = 'Tomorrow';
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
							$date      = $next->format('l');
						}
					}
				}
				if(isset($date) && isset($countdown))
				{

					echo str_replace(['{{date}}', '{{countdown}}'], [$date, $countdown], $settings['availability_text']);
				}
			}
			else
            {
	            if(empty($valid_country) && isset(\WC()->countries->countries[$country])) echo str_replace('{{postcode}}', \WC()->countries->countries[$country], $settings['postcode_unavailable_text']);
                else echo str_replace('{{postcode}}', $postcode, $settings['postcode_unavailable_text']);
            }

		}
		else
		{
			// PostCode is not set
			echo $settings['click_to_see'];
		}
		
		// Include shipping calculator to set country and postcode
		include('parts/porterbuddy-shipping-calc.php');

		// close the widget
		echo '</p><p style="float: right; margin-top: -9px;"><strong>Porter</strong>buddy</p></div>';
	}

	// Generate the product page countdown values
	function createCountdown($d, $h, $m)
	{
		$string = [];
		if($d > 0) $string[] = $d.' days';
		if($h > 0) $string[] = $h.' hours';
		if($d == 0 && $m > 0) $string[] = $m.' minutes';
		return implode(' and ', $string);
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


	// if PorterBuddy shipping is enabled, display the widget
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'woocommerce_proceed_to_checkout', 'pb_cart_display', 10 );

	function pb_cart_display() {

		// Fetch the settings
		$settings = get_option( 'woocommerce_porterbuddy-wc_settings');

		// if PorterBuddy shipping is selected, display widget
		if ( WC()->session->get('chosen_shipping_methods')[0] == PORTERBUDDY_PLUGIN_NAME )
		{
			include('parts/porterbuddy-cart-widget.php');
		}
		
	}

	/**
	 * Styling: loading stylesheets for the plugin.
	 */
	if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'wp_enqueue_scripts', 'porterbuddy_scripts', 5 );

	function porterbuddy_scripts( $page ) 
	{
		// Reigster scripts in WP
		wp_register_script( 'wp-porterbuddy-shipping-calc', plugins_url( 'js/porterbuddy-shipping-calc.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'wp-porterbuddy-scripts', plugins_url( 'js/porterbuddy-scripts.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'wp-porterbuddy-widget', plugins_url( 'js/porterbuddy-widget.js', __FILE__ ), array( 'jquery' ) );

		// localise scripts
		wp_localize_script( 'wp-porterbuddy-scripts', 'objectL10n', array(
			'countryError' => esc_html__( 'You have to select a country', 'wp-porterbuddy' ),
			'postcodeError' => esc_html__( 'Postcode must be 3 or more numbers!', 'wp-porterbuddy' ),
			'geoError' => esc_html__( 'You have blocked GEO requests in your browser and must change your settings to use your location for this.', 'wp-porterbuddy' ),
		) );

		// Register styles in WP
		wp_register_style( 'wp-porterbuddy-styles', plugins_url( 'css/porterbuddy-styles.css', __FILE__) );
		
		// Enqueue defaults
		wp_enqueue_script( 'wp-porterbuddy-scripts' );
		wp_enqueue_style( 'wp-porterbuddy-styles' );
		
	}
}