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

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Config $config
    ) {
        $this->escaper = $escaper;
        $this->config = $config;
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
                $outConfig['payment']['worldpay_payments']['ajax_check_status_url'] = $this->methods[$code]->getAjaxCheckStatus();
        }
        return $outConfig;
    }


    public function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }
}
