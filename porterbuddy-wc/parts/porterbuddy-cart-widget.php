<?php 

function pb_cart_display() {

	// fetch settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');
	// enqueue scripts
	wp_enqueue_script( 'wp-porterbuddy-widget-js' );

	// if PorterBuddy shipping is selected, display widget
	if ( is_checkout() || WC()->session->get('chosen_shipping_methods')[0] == PORTERBUDDY_PLUGIN_NAME ) :

		?>

<div id="porterbuddy-widget" class="porterbuddy-widget" data-wpnonce="<?php echo wp_create_nonce('porterbuddy_widget_options'); ?>">

	<div class="porterbuddy-widget-logo-wrapper">
		<img src="<?= plugins_url("../assets/", __FILE__) ?>porterbuddy_logo.svg" class="image porterbuddy-widget-logo" width="118" height="24" alt="Porterbuddy">
	</div>
	
	<h3 class="porterbuddy-widget-title"><?= __($settings['title'], 'porterbuddy-wc') ?></h3>
	
	<p class="porterbuddy-widget-description"><?= __($settings['description'], 'porterbuddy-wc') ?></p>
	
	<div class="porterbuddy-widget-date-selectors">
		<a href="#" class="porterbuddy-widget-date-selector prev-date unavailable"><?= __('Previous', 'porterbuddy-wc') ?></a>
		<span id="selected-date" class="porterbuddy-widget-selected-date selected-date unavailable"> </span>
		<a href="#" class="porterbuddy-widget-date-selector next-date unavailable"><?= __('Next', 'porterbuddy-wc') ?></a>
	</div>

	<div id="timeslots" class="porterbuddy-widget-timeslots">

	</div>

	<?php if ($settings['return'] == 1): ?>

		<div class="porterbuddy-widget-return">
			<label>
				<input type="checkbox" value="0" id="porterbuddy_return">
				<?= esc_html_e( $settings['return_text'], 'porterbuddy-wc' ) ?> (<?= esc_html_e( 'price', 'porterbuddy-wc' ) ?>: <span class="price"><?= $settings['return_price'] ?>,-</span>)
			</label>
		</div>

	<?php endif; ?>

	<div class="porterbuddy-widget-leave-doorstep">
		<label>
			<input type="checkbox" value="1" id="porterbuddy_leave_doorstep" checked="checked">
			<?= esc_html_e( $settings['leave_at_door'], 'porterbuddy-wc' ) ?>
		</label>
	</div>

	<div class="porterbuddy-widget-comment">
		<textarea id="porterbuddy_comment" placeholder="<?= esc_html_e( $settings['courier_message'], 'porterbuddy-wc' ) ?>" maxlength="512"></textarea>
	</div>

</div>

		<?php

	endif;
}

?>