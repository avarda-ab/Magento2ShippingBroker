<?php

namespace Avarda\ShippingBroker\Plugin\Avarda\PrepareItems\Quote;

use Avarda\Checkout3\Plugin\PrepareItems\Quote\QuoteCollectTotalsPrepareItems as PrepareItems;
use Magento\Quote\Api\Data\CartInterface;

class QuoteCollectTotalsPrepareItems
{
    /**
     * Do not add shipping item; it is already added by the Avarda shipping broker.
     */
    public function aroundPrepareShipment(): void
    {
        return;
    }

    /**
     * Shipping is skipped from the item rows, so skip it from the rounding target too.
     */
    public function afterGetRoundingTargetTotal(
        PrepareItems $subject,
        float $result,
        CartInterface $cart,
    ): float {
        if ($cart->isVirtual()) {
            return $result;
        }

        $shippingAddress = $cart->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getShippingInclTax() > 0) {
            $result -= $shippingAddress->getShippingInclTax() - $shippingAddress->getShippingDiscountAmount();
        }

        return $result;
    }
}
