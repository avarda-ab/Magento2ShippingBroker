<?php

namespace Avarda\ShippingBroker\Plugin\Avarda\PrepareItems\Quote;

use Avarda\Checkout3\Plugin\PrepareItems\Quote\QuoteCollectTotalsPrepareItems as PrepareItems;
use Magento\Quote\Api\Data\CartInterface;

class QuoteCollectTotalsPrepareItems
{
    /**
     * Do not add shipping item to as it is already added by avarda shipping broker
     *
     * @return void
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
        CartInterface $cart
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
