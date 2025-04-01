<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Avarda\Checkout3\Gateway\Helper\ItemSubjectReader;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;

/**
 * Builder for "shippingParamaters" array in the avarda shipping broker
 */
class ShippingParametersDataBuilder implements BuilderInterface
{
    public const SHIPPING_PARAMETERS = 'shippingParameters';

    public const WEIGHT = 'weight';

    private ?StoreConfigInterface $storeConfig = null;

    public function __construct(
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly StoreConfigManagerInterface $storeConfigManager
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $shippingParameters = [];
        $item = ItemSubjectReader::readItem($buildSubject);
        $qty = (int) ItemSubjectReader::readQty($buildSubject);
        try {
            $product = $this->productRepository->get($item->getSku());
            $shippingParameters[self::WEIGHT] = $this->getWeightInGrams((float) $product->getWeight()) * $qty;
        } catch (LocalizedException $e) {
        }
        if (0 == count($shippingParameters)) {
            return [];
        }

        return [
            'articleNumber' => mb_substr($item->getSku(), 0, 35),
            self::SHIPPING_PARAMETERS => $shippingParameters
        ];
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
}
