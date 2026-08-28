<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Api;

use Avarda\ShippingBroker\Api\Gateway\Request\CustomAttributeBuilderInterface;
use Avarda\ShippingBroker\Api\Gateway\Response\ParserInterface;

/**
 * Strategy contract for a shipping broker provider (e.g. nShift, Partner).
 *
 * One implementation per provider. The active provider is resolved at runtime
 * via {@see \Avarda\ShippingBroker\Model\Provider\Pool::getActive()} based on
 * the store-scoped admin config value.
 */
interface ProviderInterface
{
    /**
     * Provider code matching the value stored in admin config.
     */
    public function getCode(): string;

    /**
     * Parser used to extract the selected shipping option from the
     * Avarda getPaymentStatus response. The response shape is
     * provider-specific.
     */
    public function getResponseParser(): ParserInterface;

    /**
     * Custom attributes appended to the shippingSettings payload sent
     * into Avarda's InitializePayment / UpdateItems requests.
     *
     * @return CustomAttributeBuilderInterface[]
     */
    public function getCustomAttributesPool(): array;

    /**
     * Whether {@see \Avarda\ShippingBroker\Gateway\Request\BrokerItemsDataBuilder}
     * should append the synthetic SHI001 fallback shipping line. Required by
     * nShift; not used by Partner Shipping (Avarda configures fallback text
     * server-side).
     */
    public function shouldInjectFallbackLine(): bool;

    /**
     * Whether the frontend checkout pages should load this provider's
     * widget script. Providers that need no injected script return false.
     */
    public function shouldLoadCheckoutScript(): bool;
}
