<?php

namespace Nihaopay\Payments\Model\Methods;



class Wechatpay extends NihaopayPayments {

	protected $_code = 'nihaopay_payments_wechatpay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_giropayForm';
    protected $_isGateway = true;

    protected function myvendor(){
    	return "wechatpay";
    }
}

