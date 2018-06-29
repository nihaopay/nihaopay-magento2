<?php

namespace Nihaopay\Payments\Model\Methods;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\LocalizedException;
use Nihaopay\Payments\Model\Requestor;

class NihaopayPayments extends AbstractMethod
{
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;

    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Nihaopay\Payments\Model\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Nihaopay\Payments\Logger\Logger $wpLogger,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->config = $config;
        $this->cart = $cart;
        $this->_objectManager = $objectManager;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutData = $checkoutData;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->sessionQuote = $sessionQuote;
        $this->logger = $wpLogger;
        $this->order = $order;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    public function getOrderPlaceRedirectUrl() {
        return $this->urlBuilder->getUrl('nihaopay/securepay/redirect', ['_secure' => true]);
    }

    public function getAjaxCheckStatus() {
        return $this->urlBuilder->getUrl('nihaopay/securepay/checkstatus', ['_secure' => true]);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $_tmpData = $data->_data;
        $_serializedAdditionalData = serialize($_tmpData['additional_data']);
        $additionalDataRef = $_serializedAdditionalData;
        $additionalDataRef = unserialize($additionalDataRef);
        $_paymentToken = $additionalDataRef['paymentToken'];

        $infoInstance = $this->getInfoInstance();
        $infoInstance->setAdditionalInformation('payment_token', $_paymentToken);
        return $this;
    }

    public function createApmOrder($quote, $reference) {


        $orderId = $quote->getReservedOrderId();
        $payment = $quote->getPayment();
        $amount = $quote->getGrandTotal();

        $currency_code = $quote->getQuoteCurrencyCode();

        $orderDetails = $this->getSharedOrderDetails($quote, $currency_code);

        try {

        $debug = false;
        if ($this->config->isLiveMode()) {
            $debug = false;
        }else{
             $debug = true;
        }
        
        $token = $this->config->getServiceKey();
    
        // $sOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        // $oOrder = Mage::getModel('sales/order')->loadByIncrementId($sOrderId);
        
        $ipn = $this->urlBuilder->getUrl('nihaopay/securepay/ipn', ['_secure' => true]);
        $callback = $this->urlBuilder->getUrl('nihaopay/securepay/callback', ['_secure' => true]);

        $vendor = $this->myvendor();
        if ($orderDetails['currencyCode'] != 'JPY') {
            $amount = $amount*100;
        }
 
        $params = array("amount"=>$amount
                ,"vendor"=>$vendor
                ,"currency"=>$orderDetails['currencyCode']
                ,"reference"=> $reference
                ,"ipn_url"=>$ipn
                ,"callback_url"=>$callback
                ,"terminal" => $this->ismobile()?'WAP':'ONLINE'
                ,"description"=>$orderDetails['orderDescription']
                ,"note"=>sprintf('#%s(%s)', $orderId, $orderDetails['shopperEmailAddress'])
                );

        $this->_debug($vendor);
        $requestor = new Requestor();
        $requestor->setDebug($debug);
        $ret = $requestor->getSecureForm($token, $params);

        return $ret;
        }
        catch (\Exception $e) {

            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->_debug($e->getMessage());
            throw new \Exception('Payment failed, please try again later ' . $e->getMessage());
        }
        
    }

    function ismobile() {
        $is_mobile = '0';

        if(preg_match('/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $is_mobile=1;
        }

        if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $is_mobile=1;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
        $mobile_agents = array('w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');

        if(in_array($mobile_ua,$mobile_agents)) {
            $is_mobile=1;
        }

        if (isset($_SERVER['ALL_HTTP'])) {
            if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
                $is_mobile=1;
            }
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
            $is_mobile=0;
        }

        return $is_mobile;
    }


    protected function myvendor(){
        return "";
    }

    public function isTokenAllowed()
    {
        return true;
    }

    public function capture(InfoInterface $payment, $amount)
    {
       return $this;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        return $this;
    }

    public function refund(InfoInterface $payment, $amount)
    {
         $this->_debug("call refund");
        if ($order = $payment->getOrder()) {
           
            try {

                $debug = false;
                if ($this->config->isLiveMode()) {
                    $debug = false;
                }else{
                     $debug = true;
                }
                
                $token = $this->config->getServiceKey();

                $requestor = new Requestor();
                $requestor->setDebug($debug);
                $ret = $requestor->refund($token,$payment,$amount);

                $this->_debug("leave call refund");

                return $this;
            }
            catch (\Exception $e) {
                $this->_debug("call refund fail");
                $a = $e->getMessage();
                throw new LocalizedException(__('Refund failed ' . $e->getMessage()));
            }
        }
    }

    public function void(InfoInterface $payment)
    {
        return true;
    }

    public function cancel(InfoInterface $payment)
    {
         $this->_debug("call cancel action");
        throw new LocalizedException(__('You cannot cancel an APM order'));
    }

    

    private function getCheckoutMethod($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }
    
    public function readyMagentoQuote() {
        $quote = $this->checkoutSession->getQuote();

        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        if ($this->getCheckoutMethod($quote) == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }

        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$quote->getBillingAddress()->getEmail()
            ) {
                $quote->getBillingAddress()->setSameAsBilling(1);
            }
        }

        $quote->collectTotals();

        return $quote;
    }

    public function createMagentoOrder($quote) {
        try {
            $order = $this->quoteManagement->submit($quote);
            return $order;
        }
        catch (\Exception $e) {
            $orderId = $quote->getReservedOrderId();
            $payment = $quote->getPayment();
            $token = $payment->getAdditionalInformation('payment_token');
            $amount = $quote->getGrandTotal();
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->_debug($e->getMessage());

            \Magento\Checkout\Model\Session::restoreQuote();

            throw new \Exception($e->getMessage());
        }
    }

    public function sendMagentoOrder($order) {
        $this->checkoutSession->start();

        $this->checkoutSession->clearHelperData();

        $this->checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
    }

    protected function _debug($debugData)
    {   
        if ($this->config->debugMode($this->_code)) {
            $this->logger->debug($debugData);
        }
    }

    protected function getSharedOrderDetails($quote, $currencyCode) {

        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $items = $quote->getAllItems();

        $data = [];

       

        $data['currencyCode'] = $currencyCode;
        $data['name'] = $billing->getName();

        $data['billingAddress'] = [
            "address1"=>$billing->getStreetLine(1),
            "address2"=>$billing->getStreetLine(2),
            "address3"=>$billing->getStreetLine(3),
            "postalCode"=>$billing->getPostcode(),
            "city"=>$billing->getCity(),
            "state"=>"",
            "countryCode"=>$billing->getCountryId(),
            "telephoneNumber"=>$billing->getTelephone()
        ];

        $data['deliveryAddress'] = [
            "firstName"=>$shipping->getFirstname(),
            "lastName"=>$shipping->getLastname(),
            "address1"=>$shipping->getStreetLine(1),
            "address2"=>$shipping->getStreetLine(2),
            "address3"=>$shipping->getStreetLine(3),
            "postalCode"=>$shipping->getPostcode(),
            "city"=>$shipping->getCity(),
            "state"=>"",
            "countryCode"=>$shipping->getCountryId(),
            "telephoneNumber"=>$shipping->getTelephone()
        ];

        $Product='';
 
        foreach($items as $item) {
            $Product = $item->getName().'...';
            break;
        }
        $data['orderDescription'] = $Product;

        $data['shopperSessionId'] = $this->customerSession->getSessionId();
        $data['shopperUserAgent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data['shopperAcceptHeader'] = '*/*';

        if ($this->backendAuthSession->isLoggedIn()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($this->sessionQuote->getCustomerId());
            $data['shopperEmailAddress'] = $customer->getEmail();
        } else {
            $data['shopperEmailAddress'] = $this->customerSession->getCustomer()->getEmail();
        }
        $data['siteCode'] = null;
        $siteCodes = $this->config->getSitecodes();
        if ($siteCodes) {
            foreach ($siteCodes as $siteCode) {
                    $data['siteCode'] = $siteCode['site_code'];
                    $data['settlementCurrency'] = $siteCode['settlement_currency'];
                    break;
            }
        }
        if (!isset($data['settlementCurrency'])) {
            $data['settlementCurrency'] = $this->config->getSettlementCurrency();
        }
        return $data;
    }
}
