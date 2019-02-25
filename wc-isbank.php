<?php
/**
 * Plugin Name: WooCommerce İş Bank
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Isbank' ) ) {
	class WC_Isbank {

		protected static $instance;

		private function __construct() {
		}

		public static function instance() {
			if ( false === isset ( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->define_constants();
				self::$instance->init();
			}

			return self::$instance;
		}

		private function define_constants() {
			if ( ! defined( 'WC_ISBANK_INCLUDES' ) ) {
				define( 'WC_ISBANK_INCLUDES', dirname( __FILE__ ) . '/inc/' );
			}
		}

		private function init() {
			add_filter( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		}

		public function plugins_loaded() {
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_isbank_gateway' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'add_assets' ) );

			self::$instance->includes();
		}

		protected function includes() {
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				include_once( WC_ISBANK_INCLUDES . 'class-wc-isbank-gateway.php' );
				include_once( WC_ISBANK_INCLUDES . 'class-wc-isbank-gateway-fields.php' );
				include_once( WC_ISBANK_INCLUDES . 'class-wc-isbank-gateway-form.php' );
			}
		}

		public function add_isbank_gateway( $methods ) {
			$methods[] = 'WC_Isbank_Gateway';

			return $methods;
		}

		public function add_assets() {
			if ( is_checkout() ) {
				wp_enqueue_style(
					'woocommerce-isbank-css',
					plugins_url( '/assets/css/checkout.css', __FILE__ )
				);
			}
		}

		public static function activation() {
			if ( false === is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				wp_die(
					__( 'Eklentiyi aktif etmek için WooCommerce gerekli.', 'wc-isbank' ),
					__( 'Aktivasyon hatası - WC İşbank' ),
					array(
						'back_link' => true
					)
				);

				return;
			}
		}

	}

	WC_Isbank::instance();
	register_activation_hook( __FILE__, array( 'WC_Isbank', 'activation' ) );
}
