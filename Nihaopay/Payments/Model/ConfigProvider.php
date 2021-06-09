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
        'nihaopay_payments_alipay','nihaopay_payments_wechatpay','nihaopay_payments_unionpay'
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
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        Config $config
    ) {
        $this->escaper = $escaper;
        $this->config = $config;
        $this->_storeManager=$storeManager;
         $this->_assetRepo = $assetRepo;
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

                $outConfig['payment']['nihaopay_payments'][$code] = $this->getSkinImagePlaceholderPath($code);
        }
        return $outConfig;
    }

    /**
     * Return path for skin images placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderPath($code)
    {
        $staticPath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        $myurl = $this->methods[$code]->getImageUrl();
        $placeholderPath = $this->_assetRepo->createAsset($myurl)->getPath();
        return $staticPath . '/' . $placeholderPath;
    }


    public function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }
}
