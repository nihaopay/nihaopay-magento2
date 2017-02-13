<?php
namespace Nihaopay\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;


class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'nihaopay_payments_alipay'
    ];


    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Config
     */
    protected $config;

    protected $_storeManager;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->escaper = $escaper;
        $this->config = $config;
        $this->_storeManager=$storeManager;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $outConfig = [];    

        foreach ($this->methodCodes as $code) {
                $outConfig['payment']['nihaopay_payments']['redirect_url'] = $this->getMethodRedirectUrl($code);

                $outConfig['payment']['nihaopay_payments'][$code] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."Nihaopay_Payments/images/nihaopay_alipay/logo_en_US.png";
        }
        return $outConfig;
    }


    public function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }
}
