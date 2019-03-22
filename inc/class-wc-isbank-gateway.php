<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Gateway extends WC_Payment_Gateway {

	private $api_url = 'https://sanalpos.isbank.com.tr/fim/api';
	private $est3Dgate_url = 'https://sanalpos.isbank.com.tr/fim/est3Dgate';

	public function __construct() {
		$this->id                 = 'isbank';
		$this->title              = __( 'Kredi Kartı', 'wc-isbank' );
		$this->method_title       = __( 'Türkiye İş Bankası - WooCommerce', 'wc-isbank' );
		$this->method_description = __( '', 'wc-isbank' );
		$this->supports           = array( 'products', 'refunds' );

		$this->form_fields = WC_Isbank_Gateway_Fields::init_fields();
		$this->init_settings();

		$test_mode = $this->get_option( 'test' );

		if ( $test_mode == 'yes' ) {
			$this->api_url       = 'https://entegrasyon.asseco-see.com.tr/fim/api';
			$this->est3Dgate_url = 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate';
		}

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
			'action_url' => $this->est3Dgate_url,
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

			$xml_data = array(
				'Name'                    => $this->api_user,
				'Password'                => $this->api_user_password,
				'ClientId'                => $response['clientid'],
				'IPAdress'                => $response['clientIp'],
				'OrderId'                 => $order_id,
				'Type'                    => $response['islemtipi'],
				'Number'                  => $response['md'],
				'Amount'                  => $response['amount'],
				'Currency'                => $response['currency'],
				'PayerTxnId'              => $response['xid'],
				'PayerSecurityLevel'      => $response['eci'],
				'PayerAuthenticationCode' => $response['cavv']
			);

			$request = new WC_Isbank_Request( $this->api_url );
			$result  = $request->send( $xml_data );

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

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$xml_data = array(
			'Name'     => $this->api_user,
			'Password' => $this->api_user_password,
			'ClientId' => $this->client_id,
			'OrderId'  => $order_id,
			'Type'     => 'Credit',
			'Amount'   => $amount,
			'Currency' => '949'
		);

		$request = new WC_Isbank_Request( $this->api_url );
		$result  = $request->send( $xml_data );

		$response = (string) $result->Response;

		if ( 'Approved' === $response ) {
			return true;
		}

		return false;
	}
}
