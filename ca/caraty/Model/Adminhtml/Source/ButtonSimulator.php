<?php

namespace CA\CARaty\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ButtonSimulator implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/calc_sm_comp.png', 'label' => __('CA 163x30')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/calc_md_comp.png', 'label' => __('CA 183x38')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/calc_lg_comp.png', 'label' => __('CA 248x75')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/calc_md_full.png', 'label' => __('Crédit Agricole 250x38')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/calc_lg_full.png', 'label' => __('Crédit Agricole 323x75')],
            ['value' => '1', 'label' => __('Auto CA 163x30')],
            ['value' => '2', 'label' => __('Auto CA 183x38')],
            ['value' => '3', 'label' => __('Auto CA 248x75')],
            ['value' => '4', 'label' => __('Auto Crédit Agricole 250x38')],
            ['value' => '5', 'label' => __('Auto Crédit Agricole 323x75')],
        ];
    }
}
