<?php
if(isset($settings['enabled']) && $settings['enabled'] == 'yes') add_action( 'wp_enqueue_scripts', 'porterbuddy_scripts', 5 );

function porterbuddy_scripts( $page )
{
	// Reigster scripts in WP
	wp_register_script( 'wp-porterbuddy-shipping-calc-js', plugins_url( '../js/porterbuddy-shipping-calc.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'wp-porterbuddy-scripts-js', plugins_url( '../js/porterbuddy-scripts.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'wp-porterbuddy-widget-js', plugins_url( '../js/porterbuddy-widget.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'wp-porterbuddy-moment-js', plugins_url( '../libraries/moment-min.js', __FILE__ ) );

	// localise scripts
	wp_localize_script( 'wp-porterbuddy-scripts-js', 'objectL10n', array(
		'countryError' => esc_html__( 'You have to select a country', PORTERBUDDY_PLUGIN_NAME ),
		'postcodeError' => esc_html__( 'Postcode must be 3 or more numbers!', PORTERBUDDY_PLUGIN_NAME ),
		'geoError' => esc_html__( 'You have blocked GEO requests in your browser and must change your settings to use your location for this.', PORTERBUDDY_PLUGIN_NAME ),
		'noSlotsAvailable' => esc_html__( 'Unfortunately there are no available time slots. Please try another day.', PORTERBUDDY_PLUGIN_NAME ),
	) );
	wp_localize_script( 'wp-porterbuddy-widget-js', 'pbWidgetPHP', array(
		'ajaxphp'	=> admin_url( 'admin-ajax.php' ),
		'ajaxEndpoint'	=> plugins_url('../availability.php', __FILE__),
		'translations'	=> array(
			'ThankYou'	=>	__('Thank you', PORTERBUDDY_PLUGIN_NAME ),
		),
	) );

	// Register styles in WP
	wp_register_style( 'wp-porterbuddy-styles', plugins_url( '../css/porterbuddy-styles.css', __FILE__) );

	// Enqueue defaults
	wp_enqueue_style( 'wp-porterbuddy-styles' );
	wp_enqueue_script( 'wp-porterbuddy-moment-js' );
	wp_enqueue_script( 'wp-porterbuddy-scripts-js' );

}