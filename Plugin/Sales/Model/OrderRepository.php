<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\GatewayShipping\Plugin\Sales\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\ShippingExtensionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;

class OrderRepository
{
    public function __construct(
        protected readonly OrderExtensionFactory $orderExtensionFactory,
        protected readonly CartRepositoryInterface $quoteRepository,
        protected readonly ShippingExtensionFactory $shippingExtensionFactory
    ) {
    }

    /**
     * Adds modifications on order get
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @see \Magento\Sales\Api\OrderRepositoryInterface::get
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->addShippingMethodGateway($order);

        return $order;
    }

    /**
     * Adds modifications on get list of orders
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $result
    ) {
        foreach ($result->getItems() as $order) {
            $this->addShippingMethodGateway($order);
        }

        return $result;
    }

    /**
     * Changes order shipping method if there is avarda JSON saved in the corresponding shippingRate
     *
     * @param OrderInterface $order
     * @return void
     */
    protected function addShippingMethodGateway(OrderInterface $order)
    {
        $orderExtensionAttributes = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create();

        /** @var ShippingAssignmentInterface[] $shippingAssignments */
        $shippingAssignments = $orderExtensionAttributes->getShippingAssignments();
        if (!$shippingAssignments) {
            return;
        }

        // Get first shipping assignment
        $shippingAssignment = $shippingAssignments[0];
        $shipping = $shippingAssignment->getShipping();
        $quoteId = $order->getQuoteId();

        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getShippingMethod()) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            if ($shippingRate) {
                $shippingDetails = json_decode($shippingRate->getMethodDescription() ?? '', true);
                if (array_key_exists('carrierId', $shippingDetails ?? [])) {
                    $shipping->setMethod($shippingDetails['carrierId'] . '_' . $shippingDetails['serviceId']);
                    $shippingAssignment->setShipping($shipping);
                }
            }
        }
    }
}
