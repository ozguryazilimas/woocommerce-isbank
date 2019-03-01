<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Gateway_Form {

	public static function init_form( $order_id, $store_key, $client_id, $post ) {
		$return_url = get_home_url() . "/wc-api/wc_gateway_isbank";

		$order    = new WC_Order( $order_id );
		$amount   = $order->order_total;
		$oid      = $order_id;
		$ok_url   = $return_url;
		$fail_url = $return_url;
		$rnd      = microtime();
		$taksit   = '';
		$hashstr  = $client_id . $oid . $amount . $ok_url . $fail_url . 'Auth' . $taksit . $rnd . $store_key;
		$hash     = base64_encode( pack( 'H*', sha1( $hashstr ) ) );

		return '<form id="isbank-checkout-form" action="https://sanalpos.isbank.com.tr/fim/est3Dgate" method="post">
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
			<input type="hidden" name="pan" value="' . $_GET['pan'] . '">
			<input type="hidden" name="Ecom_Payment_Card_ExpDate_Year" value="' . $_GET['Ecom_Payment_Card_ExpDate_Year'] . '">
			<input type="hidden" name="Ecom_Payment_Card_ExpDate_Month" value="' . $_GET['Ecom_Payment_Card_ExpDate_Month'] . '">
		</form>
		<script type="text/javascript">
    		document.getElementById(\'isbank-checkout-form\').submit();
		</script>';
	}
}
