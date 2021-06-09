<?php

namespace Nihaopay\Payments\Model\Methods;



class Wechatpay extends NihaopayPayments {

	protected $_code = 'nihaopay_payments_wechatpay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_isGateway = true;

    protected function myvendor(){
    	return "wechatpay";
    }

    public function getImageUrl(){
        return "Nihaopay_Payments/images/nihaopay_wechatpay/logo_en_US.png";
    }
}
