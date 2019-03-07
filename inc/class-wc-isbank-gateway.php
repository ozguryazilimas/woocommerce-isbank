<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'isbank';
		$this->title              = __( 'Kredi Kartı', 'wc-isbank' );
		$this->method_title       = __( 'Türkiye İş Bankası - WooCommerce', 'wc-isbank' );
		$this->method_description = __( '', 'wc-isbank' );
		$this->supports           = array( 'products' );

		$this->form_fields = WC_Isbank_Gateway_Fields::init_fields();
		$this->init_settings();

		add_action(
			'woocommerce_receipt_' . $this->id,
			array( $this, 'receipt_form' )
		);

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);

		add_action( 'woocommerce_api_wc_gateway_isbank', array( $this, 'api_response' ) );

		$this->enabled           = $this->get_option( 'enabled' );
		$this->client_id         = $this->get_option( 'client_id' );
		$this->store_key         = $this->get_option( 'store_key' );
		$this->api_user          = $this->get_option( 'api_user' );
		$this->api_user_password = $this->get_option( 'api_user_password' );
	}

	public function receipt_form( $order_id ) {

		// Sepet bos ise odeme formu olusturmak yerine hata mesaji goster
		if ( WC()->cart->is_empty() ) {
			wc_add_notice( sprintf( __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'shop' ) ) ), 'error' );

			return;
		}

		$args = array(
			'form_id'    => $this->id,
			'client_id'  => $this->client_id,
			'store_key'  => $this->store_key,
			'action_url' => 'https://sanalpos.isbank.com.tr/fim/est3Dgate',
			'order_id'   => $order_id,
		);

		echo WC_Isbank_Gateway_Form::init_form( $args );
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
	}

	public function api_response() {
		global $woocommerce;
		$order_id  = $_POST['oid'];
		$order     = new WC_Order( $order_id );
		$md_status = $_POST["mdStatus"];

		if ( $md_status == "1" || $md_status == "2" || $md_status == "3" || $md_status == "4" ) {
			$response = $_POST;

			$request = '<?xml version="1.0" encoding="UTF-8"?>
				<CC5Request>
					<Name>' . $this->api_user . '</Name>
					<Password>' . $this->api_user_password . '</Password>
					<ClientId>' . $response['clientid'] . '</ClientId>
					<IPAddress>' . $response['clientIp'] . '</IPAddress>
					<OrderId>' . $order_id . '</OrderId>
					<Type>' . $response['islemtipi'] . '</Type>
					<Number>' . $response['md'] . '</Number>
					<Amount>' . $response['amount'] . '</Amount>
					<Currency>' . $response['currency'] . '</Currency>
					<PayerTxnId>' . $response['xid'] . '</PayerTxnId>
					<PayerSecurityLevel>' . $response['eci'] . '</PayerSecurityLevel>
					<PayerAuthenticationCode>' . $response['cavv'] . '</PayerAuthenticationCode>
				</CC5Request>
			';

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "https://sanalpos.isbank.com.tr/fim/api" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 90 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
			$result = curl_exec( $ch );

			$result = simplexml_load_string( $result );

			$response = (string) $result->Response;

			if ( 'Approved' === $response ) {
				$order->payment_complete();
				$woocommerce->cart->empty_cart();

				wp_redirect( $order->get_checkout_order_received_url() );
				exit;
			} else {
				$error_message = (string) $result->ErrMsg;
				wc_add_notice( $error_message, 'error' );
				$order->add_order_note( __( 'Ödeme banka tarafından reddedildi.', 'wc-isbank' ) );

				wp_redirect( $woocommerce->cart->get_cart_url() );
				exit;
			}
		} else {
			wc_add_notice( __( '3D Doğrulama başarısız.', 'wc-isbank' ), 'error' );
			wp_redirect( $woocommerce->cart->get_cart_url() );
			exit;
		}
	}
}
