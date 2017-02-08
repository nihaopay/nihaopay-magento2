<?php

namespace Nihaopay\Payments\Controller\Apm;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Model\PaymentMethod\PayPal;

class Redirect extends Apm
{
    public function execute() {
        
        $myform = "";
        $result = $this->resultJsonFactory->create();
        $quote = $this->checkoutSession->getQuote();
        $code = $quote->getPayment()->getMethod();
        $quote = $this->methods[$code]->readyMagentoQuote();
        
        try {
            $myform = $this->methods[$code]->createApmOrder($quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        $this->getResponse()->appendBody($myform);

        // /** @var \Magento\Framework\Controller\Result\Raw $response */
        // $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        // $response->setHeader('Content-type', 'text/plain');

        // $data1 = 'This is test';
        
        // $response->setContents(
        //      $myform
        // );
        // return $response;

    }
}
