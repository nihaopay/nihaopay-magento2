<?php

namespace Nihaopay\Payments\Controller\Securepay;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;


class Callback extends Apm
{
    public function execute()
    {
    	$this->_debug("enter callback");
        $data = $this->getRequest()->getParams();
        if(isset($data['status']) && $data['status']=='success'){
        	$this->_debug("Transaction successful");
            $incrementId = $this->checkoutSession->getLastRealOrderId();
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            $quoteId = $order->getQuoteId();
            $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
    	   $this->_redirect('checkout/onepage/success');

        }
        
    }
}
