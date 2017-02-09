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
             {type: 'nihaopay_payments_alipay', component: apmComponent},
             {type: 'nihaopay_payments_wechatpay', component: apmComponent},
             {type: 'nihaopay_payments_unionpay', component: apmComponent},
        ];
        $.each(methods, function (k, method) {
            rendererList.push(method);
        });

        return Component.extend({});
    }
);