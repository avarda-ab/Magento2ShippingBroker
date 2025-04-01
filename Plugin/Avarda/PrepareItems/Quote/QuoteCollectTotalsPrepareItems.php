<?php

namespace Avarda\ShippingBroker\Plugin\Avarda\PrepareItems\Quote;

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
}
