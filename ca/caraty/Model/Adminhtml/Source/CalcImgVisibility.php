<?php

namespace CA\CARaty\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CalcImgVisibility implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __(' No visible')],
            ['value' => 1,  'label' => __('Visible')],
        ];
    }
}
