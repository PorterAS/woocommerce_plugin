<?php
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