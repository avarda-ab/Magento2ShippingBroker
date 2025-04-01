<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request\CustomAttribute\Nshift;

use Avarda\ShippingBroker\Api\Gateway\Request\CustomAttributeBuilderInterface;
use Magento\Quote\Api\Data\CartInterface;

class DiscountBuilder implements CustomAttributeBuilderInterface
{
    public const ATTRIBUTE = 'discount';

    /**
     * @inheritdoc
     */
    public function build(CartInterface $cart): string
    {
        return self::ATTRIBUTE . '=' . $this->getValue($cart);
    }

    /**
     * This is just a placeholder with a default value for the discount
     *
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return string
     */
    private function getValue(CartInterface $cart): string
    {
        return '1';
    }
}
