<?php

namespace CA\CARaty\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ButtonInformation implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/inst_md_comp.png', 'label' => __('CA 183x38')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/inst_lg_comp.png', 'label' => __('CA 248x75')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/inst_md_full.png', 'label' => __('Crédit Agricole 250x38')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/inst_lg_full.png', 'label' => __('Crédit Agricole 323x75')],
        ];
    }
}
