define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function ($,
              Component,
              rendererList) {
        'use strict';

        window.WorldpayMagentoVersion = '2.0.25';
        var defaultComponent = 'Nihaopay_Payments/js/view/payment/method-renderer/card';
        var apmComponent = 'Nihaopay_Payments/js/view/payment/method-renderer/apm';
        var giropayComponent = 'Nihaopay_Payments/js/view/payment/method-renderer/giropay';
        var idealComponent = 'Nihaopay_Payments/js/view/payment/method-renderer/ideal';

        var methods = [
            {type: 'nihaopay_payments_card', component: defaultComponent},
            {type: 'nihaopay_payments_paypal', component: apmComponent},
            {type: 'nihaopay_payments_giropay', component: giropayComponent},
            {type: 'nihaopay_payments_alipay', component: apmComponent},
            {type: 'nihaopay_payments_mistercash', component: apmComponent},
            {type: 'nihaopay_payments_przelewy24', component: apmComponent},
            {type: 'nihaopay_payments_paysafecard', component: apmComponent},
            {type: 'nihaopay_payments_postepay', component: apmComponent},
            {type: 'nihaopay_payments_qiwi', component: apmComponent},
            {type: 'nihaopay_payments_sofort', component: apmComponent},
            {type: 'nihaopay_payments_yandex', component: apmComponent},
            {type: 'nihaopay_payments_ideal', component: idealComponent}
        ];
        $.each(methods, function (k, method) {
            rendererList.push(method);
        });

        return Component.extend({});
    }
);