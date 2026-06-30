<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Api\Gateway\Request;

use Magento\Quote\Api\Data\CartInterface;

interface CustomAttributeBuilderInterface
{
    /**
     * Compose attribute key=value string.
     */
    public function build(CartInterface $cart): string;
}
