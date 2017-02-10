/*browser:true*/
/*global define*/
define(
     [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, wp, setPaymentInformationAction, fullScreenLoadern, checkoutData, quote, fullScreenLoader) {
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

                        $.ajax({
                            type: 'POST',
                            url: wpConfig.ajax_check_status_url,
                            success: function (response) {
                                if (response.success) {
                                    $.mage.redirect(wpConfig.redirect_url);
                                } else {
                                    self.messageContainer.addErrorMessage({
                                        message: response.error || "Error, please try again"
                                    });
                                    fullScreenLoader.stopLoader();
                                }
                            },
                            error: function (response) {
                                fullScreenLoader.stopLoader();
                                self.messageContainer.addErrorMessage({
                                    message: "Error, please try again"
                                });
                            }
                        });


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
