<?php

namespace CA\CARaty\Controller\Index;

use CA\CARaty\Helper\OrderHelper;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;

class Success extends Action
{
    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var
     */
    protected $_customerSession;

    /**
     * @var OrderHelper
     */
    protected $_orderHelper;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * Success constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param OrderHelper $orderHelper
     * @param Cart $cart
     */
    public function __construct(Context $context, PageFactory $pageFactory, OrderHelper $orderHelper, Cart $cart)
    {
        $this->_cart = $cart;
        $this->_pageFactory = $pageFactory;
        $this->_orderHelper = $orderHelper;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $this->_orderHelper->changeOrderState(
            $this->getRequest()->getParam('orderNumber'),
            Order::STATE_PENDING_PAYMENT
        );

        return $this->_pageFactory->create();
    }
}
