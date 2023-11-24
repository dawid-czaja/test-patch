<?php

namespace CA\CARaty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var string
     */
    private $excludedCategoriesPath = 'payment/ca_raty/excluded_categories';

    /**
     * @param null|$config
     * @return array|mixed
     */
    public function getConfig($config = null)
    {
        return $this->scopeConfig->getValue($config, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function gerFormUrl() {
        return $this->_urlBuilder->getUrl('caraty/index/confirmation');
    }

    /**
     * @param $categoryIds
     * @return bool
     */
    public function isExcluded($categoryIds) {
        $excludedCategories = explode(
            ',',
            $this->scopeConfig->getValue($this->excludedCategoriesPath, ScopeInterface::SCOPE_STORE)
        );

        foreach ($categoryIds as $productCategory) {
            foreach ($excludedCategories as $excludedCategory) {
                if ($productCategory == $excludedCategory) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getProductOfferList($product, $array = false) {
        $offers = [];
        $offersJSON = $this->getConfig('payment/ca_raty/offer_id');
        $offerCategories = '';
        if ($array === true) {
            $productCategories = $product;
        } else {
            $productCategories = $product->getCategoryIds();
        }

        if (empty($productCategories)) {
            return $offers;
        }

        try {
            $offerCategories = str_replace(' ', '', json_decode($offersJSON));
            if (!is_numeric($offerCategories)) {
                $offerCategories = explode(',', $offerCategories);
            } else {
                $offerCategories[0] = $offerCategories;
            }
        } catch (\Exception $exception) {
        }

        if (empty($offerCategories)) {
            return $offers;
        }

        foreach ($offerCategories as $id => $currentOffer) {
            if (empty($currentOffer)) {
                continue;
            }

            $isCategoryMatching = false;
            $offerCategories = $currentOffer->category;
            $offerCategoriesArray = explode(',', $offerCategories);
            if (!empty($offerCategoriesArray)) {
                $offerCategories = $offerCategoriesArray;
            }

            if (empty($offerCategories)) {
                // Category is empty, so it means that always matches
                $isCategoryMatching = true;
            }

            if (!empty($offerCategories) && !empty($productCategories)) {
                foreach ($productCategories as $productCategory) {
                    if (in_array($productCategory, $offerCategories)) {
                        $isCategoryMatching = true;
                        break;
                    }
                }
            }

            // Category matches
            if ($isCategoryMatching) {
                $offers[] = [
                    'id'        => $currentOffer->id,
                    'priority'  => $currentOffer->priority
                ];
            }
        }

        if (empty($offers)) {
            $offers[] = [
                'id' => '',
                'priority' => $this->getConfig('payment/ca_raty/default_priority')
            ];
        }

        return $offers;
    }

    private function prepareProductOfferId($productOfferIds) {
        $productOfferId = null;
        if (isset($productOfferIds[0])) {
            usort($productOfferIds, function ($a, $b) {
                return (int)$b['priority'] - (int)$a['priority'];
            });

            if (isset($productOfferIds[1])) {
                if ($productOfferIds[0]['priority'] != $productOfferIds[1]['priority']) {
                    $productOfferId = $productOfferIds[0]['id'];
                }
            } else {
                $productOfferId = $productOfferIds[0]['id'];
            }
        }

        return $productOfferId;
    }

    public function getProductOfferId($product) {
        $productOfferIds = [];
        foreach ($this->getProductOfferList($product) as $offer) {
            $productOfferIds[] = array(
                'id' => $offer['id'],
                'priority' => $offer['priority'],
            );
        }

        return $this->prepareProductOfferId(array_unique($productOfferIds, SORT_REGULAR));
    }

    public function getCartOfferId($items) {
        $productOfferIds = [];
        foreach ($items as $item) {
            foreach ($this->getProductOfferList($item->getProduct())as $offer) {
                $productOfferIds[] = array(
                    'id' => $offer['id'],
                    'priority' => $offer['priority'],
                );
            }
        }

        return $this->prepareProductOfferId(array_unique($productOfferIds, SORT_REGULAR));
    }

    public function getOrderOfferId($items) {
        $productOfferIds = [];
        foreach ($items as $product) {
            foreach ($this->getProductOfferList($product['categories'], true)as $offer) {
                $productOfferIds[] = array(
                    'id' => $offer['id'],
                    'priority' => $offer['priority'],
                );
            }
        }

        return $this->prepareProductOfferId(array_unique($productOfferIds, SORT_REGULAR));
    }

    public function isAutoButton() {
        if (is_numeric($this->getConfig('payment/ca_raty/button_simulator_url'))) {
            return true;
        }

        return false;
    }

    public function getAutoButtonUrl($total, $offerId = null) {
        $url = 'http://ewniosek.credit-agricole.pl/eWniosek/button/img.png';

        $value = $url
            . '?creditAmount=' . number_format($total, 2, '.', '')
            . '&posId=' . $this->getConfig('payment/ca_raty/psp_id')
            . '&imgType=' . $this->getConfig('payment/ca_raty/button_simulator_url');

        if (!empty($offerId)) {
            $value .= '&offerId=' . $offerId;
        }

        return $value;
    }
}
