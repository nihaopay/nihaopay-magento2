/*browser:true*/
/*global define*/
define(
     [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Nihaopay_Payments/js/form/form-builder',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, wp, customerData, setPaymentInformationAction, fullScreenLoadern, checkoutData, quote, fullScreenLoader, formBuilder) {
        'use strict';
        var wpConfig = window.checkoutConfig.payment.nihaopay_payments;
        return Component.extend({
            defaults: {
                template: 'Nihaopay_Payments/form/apm',
                paymentToken: false           
            },
            initObservable: function () {
                this._super()
                    .observe('paymentToken');
                return this;
            },
            createToken: function(element, event, extraInput) {
                  
                 $.when(setPaymentInformationAction(this.messageContainer, {
                    'method': this.getCode(),
                    'additional_data': {
                        "paymentToken": this.paymentToken()
                    }
                    })).done(function () {
                        fullScreenLoader.startLoader();           

                        form = formBuilder.build(
                            {
                                action: wpConfig.redirect_url,
                                fields: []
                            }
                        );
                       
                        customerData.invalidate(['cart']);
                        form.submit();

                    }).fail(function () {
                        this.isPlaceOrderActionAllowed(true);
                    });

            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "paymentToken": this.paymentToken()
                    }
                };
            },
            getName: function() {
                return this.item.title;
            }
        });
    }
);
