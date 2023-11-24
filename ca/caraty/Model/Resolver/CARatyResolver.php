<?php

declare(strict_types=1);

namespace CA\CARaty\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use CA\CARaty\Helper\Data;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class CARatyResolver implements ResolverInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Data $helper
     * @param ValueFactory $valueFactory
     * @param CustomerFactory $customerFactory
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        Data $helper,
        ValueFactory $valueFactory,
        CustomerFactory $customerFactory,
        ServiceOutputProcessor $serviceOutputProcessor,
        ExtensibleDataObjectConverter $dataObjectConverter,
        CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->valueFactory = $valueFactory;
        $this->customerFactory = $customerFactory;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }

        try {
            $data = $this->getOrderData($args['orderId']);
            $result = function () use ($data) {
                return !empty($data) ? $data : [];
            };
            return $this->valueFactory->create($result);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    public function getOrderData($orderId) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        
        $items = $this->prepareProducts($order);
        $pspId = $this->helper->getConfig('payment/ca_raty/psp_id');
        $totalOrder = number_format(round($order->getGrandTotal(), 2), 2, '.', '');
        $randomizer = date('YmdHis') . rand();

        $hash = sprintf(
            '%sRAT2%s%s%s%s%s',
            $pspId,
            $totalOrder,
            $items[0]['name'],
            number_format(round($items[0]['price'], 2), 2, '.', ''),
            $randomizer,
            $this->helper->getConfig('payment/ca_raty/password'),
        );
        
        return [
            'items' => $items,
            'email' => $order->getCustomerEmail(),
            'PARAM_PROFILE' => $pspId,
            'orderNumber'   => $orderId,
            'PARAM_HASH'    => md5($hash),
            'randomizer'    => $randomizer,
            'PARAM_CREDIT_AMOUNT' => $totalOrder

        ];
    }

    private function prepareProducts($order) {
        $items=array();
        foreach ($order->getAllItems() as $item) {
            $items[] = array(
                'name'       => $item->getName(),
                'quantity'   => $item->getQtyOrdered(),
                'price'      => $item->getPriceInclTax(),
            );
        }

        if($order->getBaseShippingAmount() > 0) {
            $items[] = [
                'name'  => $order->getShippingDescription(),
                'quantity'   => 1,
                'price' => number_format(
                    $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount(),
                    2,
                    '.',
                    ''
                ),
            ];
        }

        if($order->getDiscountAmount() > 0) {
            $products[] = [
                'name'  => $order->getDiscountDescription(),
                'quantity'   => 1,
                'price' => number_format(
                    $order->getDiscountAmount(),
                    2,
                    '.',
                    ''
                ),
            ];
        }
        return $items;
    }
}