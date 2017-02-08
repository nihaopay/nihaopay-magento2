<?php

namespace Nihaopay\Payments\Controller\Securepay;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;


class Ipn extends Apm
{
    public function execute()
    {
        
        $this->_debug("enter ipn");
        $data = $this->getRequest()->getParams();

        $this->processPayment($data);
        $this->_redirect('checkout/onepage/success');
        $this->_debug("leave ipn");
    }



    protected function processPayment($data){
        $this->_debug('reference : ' . $data['reference']);
        if(!isset($data['reference']) || !isset($data['status'])){

            $this->_debug("503 Service Unavailable");
            return;
        }

        $order_id = '';
        $refs = explode('at',$data['reference']);
        //first item is order id
        if($refs !=null && is_array($refs)){
            $order_id = $refs[0];       
        }else{
            $this->_debug('reference code invalid:' . $data['reference']);
            return;
        }
        $this->_debug('order id : ' . $order_id);
        $order = $this->orderFactory->create()->loadByIncrementId($order_id);

        if (!$order->getId()) {
            $this->_debug("503 Service Unavailable");
            return;
        }
        $this->_debug('Find order id='.$order->getId());
        if($data['status']=='success'){
            $this->successIPN($order,$data);
        
        }else{
            $this->failIPN($order,$data);
        }
        
    }
    
    protected function successIPN($order,$data){
    
        $payment = $order->getPayment();
        $amount = ((int)$data['amount'])/100;
        $amount = number_format((float)$amount, 2, '.', '');
        $payment->setTransactionId($data['id'])
            ->setCurrencyCode($order->getOrderCurrencyCode())
            ->setPreparedMessage('')
            ->setIsTransactionClosed(1)
            ->registerCaptureNotification($amount);
        $order->save();

        // // notify customer
        // $invoice = $payment->getCreatedInvoice();
        // if ($invoice && !$order->getEmailSent()) {
        //     $order->queueNewOrderEmail()->addStatusHistoryComment(
        //         $this->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
        //     )
        //     ->setIsCustomerNotified(true)
        //     ->save();
        // }   
    }
    
    protected function failIPN($order,$data){
        $payment = $order->getPayment();

        $payment->setTransactionId($data['id'])
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true);
        if (!$order->isCanceled()) {
            // $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_DENY, false);
        } else {
            
            $comment = $this->__('Transaction ID: "%s"', $data['id']);
            $order->addStatusHistoryComment($comment, false);
        }

        $order->save();
    
    }


    public function updateOrder($status, $orderCode, $order, $payment, $amount) {

        if ($status === 'REFUNDED' || $status === 'SENT_FOR_REFUND') {
            $payment
            ->setTransactionId($orderCode)
            ->setParentTransactionId($orderCode)
            ->setIsTransactionClosed(true)
            ->registerRefundNotification($amount);

            $this->_debug('Order: ' .  $orderCode .' REFUNDED');
        }
        else if ($status === 'FAILED') {

            $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
            $payment->setStatus(self::STATUS_DECLINED);

            $this->_debug('Order: ' .  $orderCode .' FAILED');
        }
        else if ($status === 'SETTLED') {
            $this->_debug('Order: ' .  $orderCode .' SETTLED');
        }
        else if ($status === 'AUTHORIZED') {
            $payment
                ->setTransactionId($orderCode)
                ->setShouldCloseParentTransaction(1)
                ->setIsTransactionClosed(0)
                ->registerAuthorizationNotification($amount, true);
            $this->_debug('Order: ' .  $orderCode .' AUTHORIZED');
        }
        else if ($status === 'SUCCESS') {
            if($order->canInvoice()) {
                $payment
                ->setTransactionId($orderCode)
                ->setShouldCloseParentTransaction(1)
                ->setIsTransactionClosed(0);

                $invoice = $order->prepareInvoice();
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                
                $transaction = $this->transactionFactory->create();
                
                $transaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
                
                $this->invoiceSender->send($invoice);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                )
                ->setIsCustomerNotified(true);
            }
            $this->_debug('Order: ' .  $orderCode .' SUCCESS');
        }
        else {
            // Unknown status
            $order->addStatusHistoryComment('Unknown Worldpay Payment Status: ' . $status . ' for ' . $orderCode)
           ->setIsCustomerNotified(true);
        }
        $order->save();
    }


}
