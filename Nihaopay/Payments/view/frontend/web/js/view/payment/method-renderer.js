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

        var apmComponent = 'Nihaopay_Payments/js/view/payment/method-renderer/apm';
     

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