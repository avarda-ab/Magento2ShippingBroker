<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\GatewayShipping\Gateway\Request\CustomAttribute\Nshift;

use Avarda\GatewayShipping\Api\Gateway\Request\CustomAttributeBuilderInterface;
use Magento\Quote\Api\Data\CartInterface;

class FreeshippingBuilder implements CustomAttributeBuilderInterface
{
    public const ATTRIBUTE = 'freefreight';

    /**
     * @inheritdoc
     */
    public function build(CartInterface $cart): string
    {
        return self::ATTRIBUTE . '=' . $this->getValue($cart);
    }

    /**
     * Retrieve value for the attribute for given cart
     *
     * @param CartInterface $cart
     * @return string
     */
    private function getValue(CartInterface $cart): string
    {
        $cart->collectTotals();
        return $cart->getShippingAddress()->getFreeShipping() ? 'true' : 'false';
    }
}
