<?php
namespace Nihaopay\Payments\Model\Methods;

class Alipay extends WorldpayPayments {

	protected $_code = 'nihaopay_payments_alipay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'nihaopay/payment_alipayForm';
    protected $_isGateway = true;

    function myvendor(){
    	return "alipay"
    }
}
