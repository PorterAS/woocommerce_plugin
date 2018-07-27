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
		<a href="#" class="porterbuddy-widget-date-selector prev-date available">Forrige dag</a>
		<span id="selected-date" class="porterbuddy-widget-selected-date selected-date" data-datetime="2018-07-13T11:00:00+02:00">fredag 13. juli</span>
		<a href="#" class="porterbuddy-widget-date-selector next-date available">Neste dag</a>
	</div>

	<div id="timeslots" class="porterbuddy-widget-timeslots">
		<div class="porterbuddy-widget-timeslot porterbuddy-timeslot-scheduled porterbuddy-timeslot-return active" data-value="cnvporterbuddy_delivery_2018-07-13T11:00:00+02:00_2018-07-13T13:00:00+02:00_return" data-datetime="">
			<h6>11:00–13:00</h6>
			<p><span class="price">228,00</span></p>
		</div>
	
		<div class="porterbuddy-widget-timeslot porterbuddy-timeslot-scheduled porterbuddy-timeslot-return" data-value="cnvporterbuddy_delivery_2018-07-13T13:00:00+02:00_2018-07-13T15:00:00+02:00_return" data-datetime="">
			<h6>13:00–15:00</h6>
			<p><span class="price">228,00</span></p>
		</div>
	
		<div class="porterbuddy-widget-timeslot porterbuddy-timeslot-scheduled porterbuddy-timeslot-return" data-value="cnvporterbuddy_delivery_2018-07-13T15:00:00+02:00_2018-07-13T17:00:00+02:00_return" data-datetime="">
			<h6>15:00–17:00</h6>
			<p><span class="price">228,00</span></p>
		</div>
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