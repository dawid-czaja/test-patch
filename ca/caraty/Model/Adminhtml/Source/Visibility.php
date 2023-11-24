<?php

namespace CA\CARaty\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Visibility implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'visible', 'label' => __('Visible')],
            ['value' => 'hidden', 'label' => __('Hidden')],
            ['value' => 'disabled', 'label' => __('Disabled')],
        ];
    }
}
