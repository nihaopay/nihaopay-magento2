<?php

namespace Nihaopay\Payments\Controller\Securepay;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Model\PaymentMethod\PayPal;

class Redirect extends Apm
{
    public function execute() {
        
        $this->_debug("call redirect");
        $myform = "";
        $result = $this->resultJsonFactory->create();
        $quote = $this->checkoutSession->getQuote();
        $code = $quote->getPayment()->getMethod();
        $quote = $this->methods[$code]->readyMagentoQuote();
        $orderId = $quote->getReservedOrderId();
        $reference = $this->getReferenceCode($orderId);
        $order = $this->orderFactory->create()->load($orderId);

        try {
            $myform = $this->methods[$code]->createApmOrder($quote,$order,$reference);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        try {
            $order = $this->methods[$code]->createMagentoOrder($quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        if (!$order) {
            return $result->setData([
                'success' => false,
                'error' => 'Error, please try again'
            ]);
        }

        $order->addStatusHistoryComment(
            __('Redirecting user with Nihaopay Order Code  #%1.', $reference)
        )->setIsCustomerNotified(false)->save();

        $this->methods[$code]->sendMagentoOrder($order);

        $this->getResponse()->appendBody($myform);

    }


    function getReferenceCode($order_id){
        $tmstemp = time();
        return $order_id . 'at' . $tmstemp;
    }
}
