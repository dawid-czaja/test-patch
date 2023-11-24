<?php

namespace CA\CARaty\Block\Checkout;

use CA\CARaty\Helper\Data;
use CA\CARaty\Model\CARaty;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Success extends \Magento\Checkout\Block\Success {

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var float|OrderPaymentInterface|mixed|null
     */
    private $payment;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CARaty
     */
    private $caraty;

    /**
     * Success constructor.
     * @param Context $context
     * @param Data $helper
     * @param OrderFactory $orderFactory
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        OrderFactory $orderFactory,
        Session $session,
        array $data = []
    ) {
        $this->checkoutSession = $session;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;

        $this->order = $this->checkoutSession->getLastRealOrder();
        $this->payment = $this->order->getPayment();

        $this->caraty = new CARaty(
            $this->getProductList(),
            $this->helper->getConfig('payment/ca_raty/psp_id'),
            $this->helper->getConfig('payment/ca_raty/password'),
            [
                'total' => $this->order->getGrandTotal(),
                'number' => $this->order->getRealOrderId(),
                'email' => $this->order->getCustomerEmail()
            ]
        );

        parent::__construct($context, $orderFactory, $data);
    }

    /**
     * @return array
     */
    private function getProductList() {
        $products = [];
        if ($this->helper->getConfig('payment/ca_raty/validation_method') == 1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($this->order->getAllVisibleItems() as $product) {
                try {
                    $productObjectManager = $objectManager->create('Magento\Catalog\Model\Product');
                    $baseProduct = $productObjectManager->load($product->getProductId());

                    if ($this->helper->getConfig('payment/ca_raty/use_regular_price') != 1) {
                        $baseProductPrice = $baseProduct->getPriceInfo()->getPrice('final_price')->getValue();
                    } else {
                        $baseProductPrice = $baseProduct->getPriceInfo()->getPrice('regular_price')->getValue();
                    }

                    if ($this->helper->getConfig('payment/ca_raty/calculate_tax') != 1) {
                        $formPrice = $baseProductPrice;
                    } else {
                        $formPrice = $baseProductPrice + $baseProductPrice * ($product->getTaxPercent() / 100);
                    }

                    $products[] = [
                        'name' => $product->getName(),
                        'price' => number_format(
                            $formPrice,
                            2,
                            '.',
                            ''
                        ),
                        'qty' => (int)$product->getQtyOrdered(),
                        'categories' => $baseProduct->getCategoryIds(),
                    ];
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($this->order->getAllVisibleItems() as $product) {
                $productObjectManager = $objectManager->create('Magento\Catalog\Model\Product');
                $baseProduct = $productObjectManager->load($product->getProductId());
                $products[] = [
                    'name' => $product->getName(),
                    'price' => number_format(
                        $product->getPriceInclTax(),
                        2,
                        '.',
                        ''
                    ),
                    'qty' => (int)$product->getQtyOrdered(),
                    'categories' => $baseProduct->getCategoryIds(),
                ];
            }
        }

        return $products;
    }

    private function prepareProducts() {
        $products = $this->getProductList();
        $productsQuantity = 0;

        foreach($products as $product) {
            $productsQuantity += $product['qty'];
        }
        if($this->order->getBaseShippingAmount() > 0) {
            $products[] = [
                'name'  => $this->order->getShippingDescription(),
                'qty'   => 1,
                'price' => number_format(
                    $this->order->getBaseShippingAmount() + $this->order->getBaseShippingTaxAmount(),
                    2,
                    '.',
                    ''
                ),
            ];
            $productsQuantity++;
        }
        if($this->order->getDiscountAmount() > 0) {
            $products[] = [
                'name'  => $this->order->getDiscountDescription(),
                'qty'   => 1,
                'price' => number_format(
                    $this->order->getDiscountAmount(),
                    2,
                    '.',
                    ''
                ),
            ];
            $productsQuantity++;
        }

        if($productsQuantity < 100) {
            return $products;
        }

        $products = [];
        $products[] = [
            'name'  => 'Zamówienie numer ' . $this->order->getRealOrderId(),
            'qty'   => 1,
            'price' => number_format(
                $this->order->getGrandTotal(),
                2,
                '.',
                ''
            ),
        ];
        $this->caraty->setFirstProduct($products);
        return $products;
    }

    /**
     * @return array
     */
    public function getOrderData() {
        return [
            'items' => $this->prepareProducts(),
            'email' => $this->order->getCustomerEmail(),
            'PARAM_PROFILE' => $this->helper->getConfig('payment/ca_raty/psp_id'),
            'orderNumber'   => $this->order->getRealOrderId(),
            'PARAM_CREDIT_AMOUNT'   => number_format(
                $this->order->getGrandTotal(),
                2,
                '.',
                ''
            ),
            'PARAM_HASH'    => $this->caraty->getHash(),
            'randomizer'    => $this->caraty->getRandomValue()
        ];
    }

    /**
     * @return string
     */
    public function getPaymentMethodTitle() {
        return $this->payment->getMethodInstance()->getTitle();
    }
}
