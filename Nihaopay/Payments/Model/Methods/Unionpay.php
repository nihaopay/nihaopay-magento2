<?php
namespace Nihaopay\Payments\Model\Methods;

class Unionpay extends WorldpayPayments {

	protected $_code = 'nihaopay_payments_unionpay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_isGateway = true;
}
