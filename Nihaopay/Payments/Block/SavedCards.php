<?php
namespace Nihaopay\Payments\Block;

class SavedCards extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Nihaopay_Payments::saved_cards.phtml';
    protected $config;
    protected $urlBuilder;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Nihaopay\Payments\Model\Config $config,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    public function getClientKey()
    {
        return $this->config->getClientKey();
    }

}
