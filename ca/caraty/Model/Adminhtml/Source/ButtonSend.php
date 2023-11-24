<?php

namespace CA\CARaty\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ButtonSend implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/fill_md_comp.png', 'label' => __('CA 193x38')],
            ['value' => 'https://ewniosek.credit-agricole.pl/eWniosek/res/buttons/fill_md_full.png', 'label' => __('Crédit Agricole 250x38')],
        ];
    }
}
