<?php

namespace Avarda\ShippingBroker\Observer;

use Avarda\ShippingBroker\Model\Carrier\Avarda;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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

        if (!$quoteId) {
            return;
        }

        try {
            // If old order, the quote might not exist anymore, but then the update is not necessary
            $quote = $this->quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        $shippingData = $shippingAddress->getShippingRateByCode($shippingMethod);

        if ($shippingData && $shippingData->getMethod() === Avarda::METHOD_CODE) {
            $pickupPoint = $shippingData->getMethodDescription();
            if ($pickupPoint) {
                $order->getShippingAddress()->setNshiftPickupPoint($pickupPoint);
            }
        }
    }
}
