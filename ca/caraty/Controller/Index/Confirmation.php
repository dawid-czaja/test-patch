<?php

namespace CA\CARaty\Controller\Index;

use CA\CARaty\Helper\OrderHelper;
use Exception;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class Confirmation extends Action
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * Success constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param OrderHelper $orderHelper
     * @param Quote $quote
     */
    public function __construct(Context $context, PageFactory $pageFactory, OrderHelper $orderHelper, Quote $quote)
    {
        $this->quote = $quote;
        $this->pageFactory = $pageFactory;
        $this->orderHelper = $orderHelper;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     * @throws Exception
     */
    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->quote->getId() == $this->getRequest()->getParam('orderNumber', null)) {
                if ($this->quote->getShippingAddress()->getSameAsBilling()) {
                    $this->quote->setBillingAddress($this->quote->getShippingAddress());
                }
                if (empty($this->quote->getCustomerEmail())) {
                    $email = $this->getRequest()->getParam('email', null);
                    $this->quote->setCustomerId(null);
                    $this->quote->setCustomerEmail($email);
                    $this->quote->setCustomerIsGuest(true);
                    $this->quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
                }

                $this->orderHelper->createOrder($this->quote->getId());

                $this->orderHelper->changeOrderState(
                    $this->getRequest()->getParam('orderNumber'),
                    Order::STATE_PROCESSING
                );

                return $this->pageFactory->create();
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('');
    }
}
