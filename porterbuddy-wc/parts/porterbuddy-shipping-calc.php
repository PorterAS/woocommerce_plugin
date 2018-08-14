<?php

if( is_product() ):

	wp_enqueue_script( 'wc-country-select' );
	wp_enqueue_script( 'wp-porterbuddy-shipping-calc-js' );

	$form_postcode = isset($_COOKIE['pb_postcode']) && $_COOKIE['pb_postcode'] == 'x' ? null : (
			WC()->customer->get_shipping_postcode() != null ? WC()->customer->get_shipping_postcode() : ( 
				isset($_COOKIE['pb_postcode']) ? $_COOKIE['pb_postcode'] : ''
			)
		);

	$form_country = isset($_COOKIE['pb_country']) && $_COOKIE['pb_country'] == 'x' ? null : (
			WC()->customer->get_shipping_country() != null ? WC()->customer->get_shipping_country() : ( 
				isset($_COOKIE['pb_country']) ? $_COOKIE['pb_country'] : ''
			)
		);

	?>

	<form class="woocommerce-shipping-calculator" <?= "data-geo=".$settings["geo_widget"]; ?> action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

		<p><a href="#" class="shipping-calculator-button"><?php if ($form_postcode == null) esc_html_e( 'Check eligibility', 'porterbuddy-wc' ); else esc_html_e( 'Change postcode', 'porterbuddy-wc' ); ?> (<?php echo $form_postcode; ?>)</a></p>

		<section class="shipping-calculator-form" style="display:none;">

			<p class="form-row form-row-wide" id="calc_shipping_country_field">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
					<option value=""><?php esc_html_e( 'Select a country&hellip;', 'woocommerce' ); ?></option>
					<?php
					foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( $form_country, esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</p>

			<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) ) : ?>

				<p class="form-row form-row-wide" id="calc_shipping_postcode_field">
					<input type="text" class="input-text" value="<?php echo esc_attr( $form_postcode ); ?>" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'woocommerce' ); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
				</p>

			<?php endif; ?>

			<p>
				<button type="submit" name="calc_shipping" value="1" class="button"><?php esc_html_e( 'Update postcode', 'porterbuddy-wc' ); ?></button>
				<?php if ( $form_postcode ) : ?>
					<button type="button" class="use_geo_btn"><?php esc_html_e( 'Use your location', 'porterbuddy-wc' ); ?></button>
				<?php endif; ?>
			</p>

			<?php wp_nonce_field( 'woocommerce-cart' ); ?>
		</section>
	</form>

	<div class="porterbuddy-widget-logo-wrapper porterbuddy-product">
		<img src="<?= plugins_url("../assets/", __FILE__) ?>porterbuddy_logo.svg" class="image porterbuddy-widget-logo" width="118" height="24" alt="Porterbuddy">
	</div>

<?php endif; ?>