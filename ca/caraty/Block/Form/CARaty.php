<?php

namespace CA\CARaty\Block\Form;

use Magento\Payment\Block\Form;

class CARaty extends Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'CA_CARaty::form/caraty-form.phtml';
}
