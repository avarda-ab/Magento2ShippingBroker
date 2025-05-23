<?php

namespace Avarda\ShippingBroker\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class SavePickupPointToOrder implements ObserverInterface
{
    protected CartRepositoryInterface $quoteRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();

        $quote = $this->quoteRepository->get($quoteId);
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        $shippingData = $shippingAddress->getShippingRateByCode($shippingMethod);

        if ($shippingData && $shippingData->getMethod() === 'shipping_broker') {
            $pickupPoint = $shippingData->getMethodDescription();
            if ($pickupPoint) {
                $order->getShippingAddress()->setNshiftPickupPoint($pickupPoint);
            }
        }
    }
}
