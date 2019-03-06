$(document).ready(function () {
    $('.wc-isbank-checkout').submit(function (e) {
        e.preventDefault();
        e.stopPropagation();

        let $form = $(this);

        if ($form.is('.processing')) {
            return false;
        }

        $form.addClass('processing');

        $form.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'validate_isbank_form',
                pan: $form.find('input[name="pan"]').val(),
                card_cvc: $form.find('input[name="isbank-card-cvc"]').val(),
                card_expriy: $form.find('input[name="isbank-card-expiry"]').val()
            },
            dataType: 'json',
            success: function (result) {
                if ('success' === result.result) {

                    let card_expriy = $form.find('input[name="isbank-card-expiry"]').val();
                    card_expriy = card_expriy.split(' / ');

                    $form.find('#wc-isbank-cc-form').append('<input type="hidden" value="' + card_expriy[0] + '" name="Ecom_Payment_Card_ExpDate_Month">');
                    $form.find('#wc-isbank-cc-form').append('<input type="hidden" value="' + card_expriy[1] + '" name="Ecom_Payment_Card_ExpDate_Year">');

                    e.currentTarget.submit();
                } else if ('failure' === result.result) {
                    submit_error(result.msg);
                }
            },
            error: function (result) {
            }
        });
    });

    function submit_error(error_message) {
        let $form = $('.wc-isbank-checkout');
        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        $form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error"><li>' + error_message + '</li></ul></div>');
        $form.removeClass('processing').unblock();
        $form.find('.input-text, select, input:checkbox').trigger('validate').blur();
        $('html, body').animate({
            scrollTop: ($('form.wc-isbank-checkout').offset().top - 100)
        }, 1000);
        $(document.body).trigger('checkout_error');
    }
});
