<?php
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

				$this->form_fields = include dirname(__FILE__).'/options.php';

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
				/*
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
				/*
				var_dump(json_encode($buddy->checkAvailability(
					$origin_address,
					$destination_address,
					$windows,
					$parcels,
					['delivery', 'express']
				)));
				*/
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