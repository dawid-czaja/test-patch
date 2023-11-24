<?php

namespace CA\CARaty\Model\Ui;

use CA\CARaty\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'ca_raty';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * ConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param Cart $cart
     * @param Session $customerSession
     * @param Data $helper
     */
    public function __construct(StoreManagerInterface $storeManager, Cart $cart, Session $customerSession, Data $helper) {
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    private function getProductList() {
        $products = [];
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $product) { //TODO may be wrong
            $categories = $product->getProduct()->getCategoryIds();
            array_walk($categories, function ($category) {
                return (int)$category;
            });

            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'price' => number_format(
                    $product->getBasePrice(),
                    2,
                    '.',
                    ''
                ),
                'qty' => $product->getQty(),
                'categories' => $categories,
            ];
        }

        return $products;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig() {
        return [
            'ca_raty' => [
                'email'         => $this->customerSession->getCustomer()->getEmail(),
                'formUrl'       => $this->helper->gerFormUrl(),
                'isCartValid'   => $this->isCartValid($this->getProductList()),
                'orderNumber'   => $this->cart->getQuote()->getId(),
            ]
        ];
    }

    /**
     * @param $products
     * @return bool
     */
    public function isCartValid($products) {
        $excluded = false;
        $totalPrice = 0;
        foreach ($products as $product) {
            $totalPrice += $product['price'] * $product['qty'];
            if ($this->helper->isExcluded($product['categories']) === true) {
                $excluded = true;
                break;
            }
        }

        if (
            $this->helper->getConfig('payment/ca_raty/min_cart_value') < $totalPrice
            && $this->helper->getConfig('payment/ca_raty/active') == 1
            && $excluded === false
        ) {
            return true;
        }

        return false;
    }
}
