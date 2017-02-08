<?php

namespace Nihaopay\Payments\Controller\Securepay;
use Magento\Payment\Helper\Data as PaymentHelper;

abstract class Apm extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $resultJsonFactory;

    protected $orderFactory;
    protected $methods = [];

    protected $wordpayPaymentsCard;

    protected $methodCodes = [
        'nihaopay_payments_alipay'
    ];

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Nihaopay\Payments\Model\Methods\WorldpayPayments $Worldpay,
        PaymentHelper $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        $params = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
}
