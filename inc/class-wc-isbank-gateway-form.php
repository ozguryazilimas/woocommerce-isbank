<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Gateway_Form {

	public static function init_form( $args ) {
		$form     = new WC_Payment_Gateway_CC();
		$form->id = $args['form_id'];

		$order      = new WC_Order( $args['order_id'] );
		$return_url = get_home_url() . "/wc-api/wc_gateway_isbank";
		$amount     = $order->order_total;
		$rnd        = microtime();
		$taksit     = '';
		$hashstr    = $args['client_id'] . $args['order_id'] . $amount . $return_url . $return_url . 'Auth' . $taksit . $rnd . $args['store_key'];
		$hash       = base64_encode( pack( 'H*', sha1( $hashstr ) ) );

		$form_css = 'wc-isbank-checkout woocommerce-checkout';
		$form_css = apply_filters( 'woocoomerce_isbank_css', $form_css );

		wp_enqueue_script( 'wc-credit-card-form' );
		ob_start();
		?>
        <form action="<?php echo $args['action_url']; ?>" class="<?php echo $form_css; ?>" method="post">
            <div id="payment" class="woocommerce-checkout-payment">
                <ul class="wc_payment_methods payment_methods methods">
                    <li class="wc_payment_method payment_method_cod">
                        <div class="payment_box payment_method_isbank">
                            <fieldset id="wc-isbank-cc-form" class='wc-credit-card-form wc-payment-form'>

                                <p class="form-row form-row-wide">
                                    <label for="isbank-card-number">
                                        <?php echo __( 'Kart numarası', 'wc-isbank' ); ?>
                                        <span class="required">*</span>
                                    </label>

                                    <input id="isbank-card-number" class="input-text wc-credit-card-form-card-number"
                                           inputmode="numeric" autocomplete="cc-number" autocorrect="no"
                                           autocapitalize="no" spellcheck="no" type="tel"
                                           placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;"
                                           name="pan"/>
                                </p>

                                <p class="form-row form-row-first">
                                    <label for="isbank-card-expiry">
                                        <?php echo __( 'Vade (MM / YYYY)', 'wc-isbank' ); ?>
                                        <span class="required">*</span>
                                    </label>

                                    <input id="isbank-card-expiry"
                                           class="input-text wc-credit-card-form-card-expiry"
                                           inputmode="numeric" autocomplete="cc-exp" autocorrect="no"
                                           autocapitalize="no" spellcheck="no" type="tel" placeholder="MM/YYYY"
                                           name="isbank-card-expiry"/>
                                </p>
                                <p class="form-row form-row-last">
                                    <label for="isbank-card-cvc">
                                        <?php echo __( 'Güvenlik Kodu', 'wc-isbank' ); ?>
                                        <span class="required">*</span>
                                    </label>

                                    <input id="isbank-card-cvc" class="input-text wc-credit-card-form-card-cvc"
                                           inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no"
                                           spellcheck="no" type="tel" maxlength="4" placeholder="CVC"
                                           name="isbank-card-cvc" style="width:75px"/>
                                </p>
                                <div class="clear"></div>

                                <input type="hidden" name="clientid" value="<?php echo $args['client_id'] ?>"/>
                                <input type="hidden" name="amount" value="<?php echo $amount; ?>"/>
                                <input type="hidden" name="oid" value="<?php echo $args['order_id']; ?>"/>
                                <input type="hidden" name="okUrl" value="<?php echo $return_url; ?>"/>
                                <input type="hidden" name="failUrl" value="<?php echo $return_url; ?>"/>
                                <input type="hidden" name="rnd" value="<?php echo $rnd; ?>"/>
                                <input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
                                <input type="hidden" name="storetype" value="3D"/>
                                <input type="hidden" name="lang" value="tr"/>
                                <input type="hidden" name="currency" value="949"/>
                                <input type="hidden" name="islemtipi" value="Auth"/>
                                <input type="hidden" name="taksit" value=""/>
                            </fieldset>
                        </div>
                    </li>
                    <input type="submit" class="button alt" value="<?php echo __( 'Siparişi onayla', 'wc-isbank' ); ?>"/>
                </ul>
            </div>
        </form>
		<?php
		$html = ob_get_clean();

		return $html;
	}

	public static function validate_fields() {
		if ( empty( $_POST['pan'] ) ||
		     empty( $_POST['card_expriy'] ) ||
		     empty( $_POST['card_cvc'] ) ) {

			echo json_encode( array(
				'result' => 'failure',
				'msg'    => __( 'Tüm ödeme bilgi alanlarını doldurmalısın.', 'wc-isbank' )
			) );
			wp_die();
		}

		$expiry_date = $_POST['card_expriy'];
		$expiry_date = explode( ' / ', $expiry_date );

		if ( strlen( $expiry_date[1] ) < 4 ) {
			echo json_encode( array(
				'result' => 'failure',
				'msg'    => __( 'Kart vade yılını 4 basamaklı girmelisin.', 'wc-isbank' )
			) );
			wp_die();
		}

		if ( ( $expiry_date[0] < date( 'm' ) && $expiry_date[1] == date( 'Y' ) ) || $expiry_date[1] < date( 'Y' ) ) {

			echo json_encode( array(
				'result' => 'failure',
				'msg'    => __( 'Vadesi dolmuş kart ile ödeme yapamazsın.', 'wc-isbank' )
			) );
			wp_die();
		}

		echo json_encode( array(
			'result' => 'success'
		) );
		wp_die();
	}
}
