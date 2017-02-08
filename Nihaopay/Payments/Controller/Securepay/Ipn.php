<?php

namespace Nihaopay\Payments\Controller\Securepay;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;


class Ipn extends Apm
{
    public function execute()
    {
        
        // $order_id = '';
        // $refs = explode('at',$data['reference']);
        // //first item is order id
        // if($refs !=null && is_array($refs)){
        //     $order_id = $refs[0];       
        // }else{
        //     $this->log('reference code invalid:' . $data['reference']);
        //     return;
        // }

        $incrementId = $this->checkoutSession->getLastRealOrderId();
        
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $quoteId = $order->getQuoteId();
        sleep(1);
        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $worldpayClass = $this->wordpayPaymentsCard->setupWorldpay();
        $wpOrder = $worldpayClass->getOrder($wordpayOrderCode);
        $payment = $order->getPayment();
        $amount = $wpOrder['amount']/100;
        $this->wordpayPaymentsCard->updateOrder($wpOrder['paymentStatus'], $wpOrder['orderCode'], $order, $payment, $amount);

        // $this->orderSender->send($order);


        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
        $this->_redirect('checkout/onepage/success');
    }
}
