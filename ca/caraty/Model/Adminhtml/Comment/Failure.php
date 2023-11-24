<?php

namespace CA\CARaty\Model\Adminhtml\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Failure implements CommentInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Failure constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * @param string $elementValue
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCommentText($elementValue)
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        return $baseUrl . 'caraty/index/failure';
    }
}
