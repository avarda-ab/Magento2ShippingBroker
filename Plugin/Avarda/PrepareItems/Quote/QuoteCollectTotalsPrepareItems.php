<?php

namespace Avarda\ShippingBroker\Plugin\Avarda\PrepareItems\Quote;

class QuoteCollectTotalsPrepareItems
{
    /**
     * Do not add shipping item; it is already added by the Avarda shipping broker.
     */
    public function aroundPrepareShipment(): void
    {
        return;
    }
}
