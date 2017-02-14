<?php
namespace Nihaopay\Payments\Model\Methods;

class Unionpay extends NihaopayPayments {

	protected $_code = 'nihaopay_payments_unionpay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_isGateway = true;

    protected function myvendor(){
    	return "unionpay";
    }

    public function getImageUrl(){
        return "Nihaopay_Payments/images/nihaopay_unionpay/logo_en_US.png";
    }
}
