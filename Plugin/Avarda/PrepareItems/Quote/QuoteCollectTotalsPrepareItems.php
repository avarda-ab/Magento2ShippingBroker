<?php

namespace Avarda\GatewayShipping\Plugin\Avarda\PrepareItems\Quote;

class QuoteCollectTotalsPrepareItems
{
    /**
     * Do not add shipping item to as it is already added by shipping gateway
     *
     * @return void
     */
    public function aroundPrepareShipment(): void
    {
        return;
    }
}
