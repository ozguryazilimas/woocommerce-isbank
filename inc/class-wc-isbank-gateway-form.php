<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Gateway_Form {

	public static function init_form( $order_id, $store_key, $client_id ) {
		$return_url = get_home_url() . "/wc-api/wc_gateway_iyzico";

		$order    = new WC_Order( $order_id );
		$amount   = $order->order_total;
		$oid      = $order_id;
		$ok_url   = $return_url;
		$fail_url = $return_url;
		$rnd      = microtime();
		$taksit   = '';
		$hashstr  = $client_id . $oid . $amount . $ok_url . $fail_url . 'Auth' . $taksit . $rnd . $store_key;
		$hash     = base64_encode( pack( 'H*', sha1( $hashstr ) ) );

		$form_css = '';
		$form_css = apply_filters( 'woocoomerce_isbank_css', $form_css );

		return '<form class="' . $form_css . ' wc-isbank-checkout" action="https://entegrasyon.asseco-see.com.tr/fim/est3Dgate" method="post">
			<input type="hidden" name="clientid" value="' . $client_id . '">
			<input type="hidden" name="amount" value="' . $amount . '">
			<input type="hidden" name="oid" value="' . $oid . '">
			<input type="hidden" name="okUrl" value="' . $ok_url . '">
			<input type="hidden" name="failUrl" value="' . $fail_url . '">
			<input type="hidden" name="rnd" value="' . $rnd . '">
			<input type="hidden" name="hash" value="' . $hash . '" >
			<input type="hidden" name="storetype" value="3D" >
			<input type="hidden" name="lang" value="tr">
			<input type="hidden" name="currency" value="949">
			<input type="hidden" name="islemtipi" value="Auth">
			<input type="hidden" name="taksit" value="">

			<label>Card Number</label>
			<input type="text" id="pan" name="pan" maxlength="16" />

			<label>CVV</label>
			<input type="text" name="cv2" maxlength="3" value="" />

			<label>Expiration Date Year</label>
			<input type="text" name="Ecom_Payment_Card_ExpDate_Year" minlength="4" maxlength="4" value="" />

			<label>Expiration Date Month</label>
			<input type="text" name="Ecom_Payment_Card_ExpDate_Month" minlength="2" maxlength="2" value="" />

			<label>Choosing Visa / Master Card</label>
			<select name="cardType">
				<option value="1">Visa</option>
				<option value="2">MasterCard</option>
			</select>

			<input type="submit" value="Complete Payment" />
		
		</form>';
	}
}
