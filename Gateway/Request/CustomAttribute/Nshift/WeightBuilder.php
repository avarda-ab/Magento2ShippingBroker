<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request\CustomAttribute\Nshift;

use Avarda\ShippingBroker\Api\Gateway\Request\CustomAttributeBuilderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;

class WeightBuilder implements CustomAttributeBuilderInterface
{
    public const ATTRIBUTE = 'weight';

    /**
     * @var StoreConfigInterface
     */
    private $storeConfig;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     */
    public function __construct(
        private readonly StoreConfigManagerInterface $storeConfigManager
    ) {
    }

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
        $weight = 0;
        foreach ($cart->getItems() as $item) {
            $weight += $this->getWeightInGrams((float) $item->getWeight() * $item->getQty());
        }

        return (string) $weight;
    }

    /**
     * Calculate weight in grams from what is currently set in magento
     *
     * @param float $weight
     * @return int
     */
    public function getWeightInGrams(float $weight): int
    {
        if ($this->getStoreConfig()->getWeightUnit() == 'kgs') {
            return (int) ($weight * 1000);
        } else {
            return (int) ($weight * 453.592);
        }
    }

    /**
     * Retrieve Store Config
     *
     * @return StoreConfigInterface
     */
    private function getStoreConfig(): StoreConfigInterface
    {
        if (!$this->storeConfig) {
            $storeConfigs = $this->storeConfigManager->getStoreConfigs();
            $this->storeConfig = current($storeConfigs);
        }

        return $this->storeConfig;
    }
}
