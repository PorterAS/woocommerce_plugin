<?php 

function pb_cart_display() {

	// fetch settings
	$settings = get_option( 'woocommerce_porterbuddy-wc_settings');
	// enqueue scripts
	wp_enqueue_script( 'wp-porterbuddy-widget-js' );

	// if PorterBuddy shipping is selected, display widget
	if ( WC()->session->get('chosen_shipping_methods')[0] == PORTERBUDDY_PLUGIN_NAME ) :

		?>

<div id="porterbuddy-widget" class="porterbuddy-widget">

	<div class="porterbuddy-widget-logo-wrapper">
		<img src="<?= plugins_url("../assets/", __FILE__) ?>porterbuddy_logo.svg" class="image porterbuddy-widget-logo" width="118" height="24" alt="Porterbuddy">
	</div>
	
	<h3 class="porterbuddy-widget-title"><?= $settings['title'] ?></h3>
	
	<p class="porterbuddy-widget-description"><?= $settings['description'] ?></p>
	
	<div class="porterbuddy-widget-date-selectors">
		<a href="#" class="porterbuddy-widget-date-selector prev-date unavailable">Forrige dag</a>
		<span id="selected-date" class="porterbuddy-widget-selected-date selected-date unavailable"><?= strftime("%A %e %b") ?></span>
		<a href="#" class="porterbuddy-widget-date-selector next-date unavailable">Neste dag</a>
	</div>

	<div id="timeslots" class="porterbuddy-widget-timeslots">

	</div>

	<div class="porterbuddy-widget-return">
		<label>
			<input type="checkbox" value="1" id="porterbuddy_return">
			Retur on-demand; Budet venter inntil 10 minutter og tar varer med seg varer i retur on ønskelig (pris <span class="price">79,00</span>)
		</label>
	</div>

	<div class="porterbuddy-widget-leave-doorstep">
		<label>
			<input type="checkbox" value="1" id="porterbuddy_leave_doorstep" checked="checked">
			Budet kan levere pakken utenfor døren på leveranse adressen
		</label>
	</div>

	<div class="porterbuddy-widget-comment">
		<textarea id="porterbuddy_comment" placeholder="Evt. beskjet til budet" maxlength="512"></textarea>
	</div>
</div>

		<?php 

	endif;
}

?>