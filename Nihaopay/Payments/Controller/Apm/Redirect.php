<?php

namespace Nihaopay\Payments\Controller\Apm;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Model\PaymentMethod\PayPal;

class Redirect extends Apm
{
    public function execute() {
        
        $redirectUrl = false;
        $result = $this->resultJsonFactory->create();
        $quote = $this->checkoutSession->getQuote();
        $code = $quote->getPayment()->getMethod();
        $quote = $this->methods[$code]->readyMagentoQuote();
        

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
        $data1 = 'This is test';
        $data2 = 20;
        $response->setContents(
             $data1
        );
        return $response;

    }
}
