<?php

namespace Nihaopay\Payments\Model\Resource\SavedCard;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Nihaopay\Payments\Model\SavedCard', 'Nihaopay\Payments\Model\Resource\SavedCard');
    }
 
    
}