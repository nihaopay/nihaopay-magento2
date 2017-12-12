<?php
namespace Nihaopay\Payments\Block\Form;

class Card extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Nihaopay_Payments::form/card.phtml';
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Nihaopay\Payments\Model\Config $config,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function getClientKey()
    {
        return $this->config->getClientKey();
    }

}