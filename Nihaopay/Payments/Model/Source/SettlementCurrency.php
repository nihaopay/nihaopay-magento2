<?php

namespace Nihaopay\Payments\Model\Source;

use Magento\Framework\Option\ArrayInterface;


class SettlementCurrency implements ArrayInterface {

    const RMB_CURRENCY_VALUE = 'CNY';
    const JPY_CURRENCY_VALUE = 'JPY';

	public function toOptionArray() {
        return [
            ['value' => 'USD', 'label' => 'US Dollar'],
            ['value' => 'JPY', 'label' => 'Japanese Yen'],
            ['value' => 'GBP', 'label' => 'British Pound'],
            ['value' => 'EUR', 'label' => 'Euro'],
            ['value' => 'HKD', 'label' => 'Hong Kong Dollar'],
            ['value' => 'CAD', 'label' => 'Canadian Dollar']
        ];
	}

}
