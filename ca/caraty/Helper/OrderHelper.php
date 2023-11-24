<?php

namespace CA\CARaty\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderHelper extends AbstractHelper
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagementInterface;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepositoryInterface;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * OrderHelper constructor.
     * @param Context $context
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param CartManagementInterface $cartManagementInterface
     * @param Order $order
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        CartRepositoryInterface $cartRepositoryInterface,
        CartManagementInterface $cartManagementInterface,
        Order $order,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $messageManager
    )
    {
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * @param $quoteId
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function createOrder($quoteId)
    {
        $quote = $this->cartRepositoryInterface->get($quoteId);
        $quote->getPayment()->importData(['method' => 'ca_raty']);
        $orderId = $this->cartManagementInterface->placeOrder($quoteId);
        $order = $this->order->load($orderId);

        $order->setEmailSent(0);
        $order->getRealOrderId();
    }

    /**
     * @param $orderId
     * @param $newOrderState
     */
    public function changeOrderState($orderId, $newOrderState)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $order->setState($newOrderState);
            $order->setStatus($newOrderState);
            $this->orderRepository->save($order);
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
    }
}
