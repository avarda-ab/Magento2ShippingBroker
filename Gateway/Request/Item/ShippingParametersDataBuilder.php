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

    protected ProductRepositoryInterface $productRepository;
    protected StoreConfigManagerInterface $storeConfigManager;
    protected ?StoreConfigInterface $storeConfig = null;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreConfigManagerInterface $storeConfigManager
    ) {
        $this->productRepository = $productRepository;
        $this->storeConfigManager = $storeConfigManager;
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

    private function getStoreConfig(): StoreConfigInterface
    {
        if (!$this->storeConfig) {
            $storeConfigs = $this->storeConfigManager->getStoreConfigs();
            $this->storeConfig = current($storeConfigs);
        }

        return $this->storeConfig;
    }

    public function getWeightInGrams(float $weight): int
    {
        if ($this->getStoreConfig()->getWeightUnit() == 'kgs') {
            return (int) ($weight * 1000);
        } else {
            return (int) ($weight * 453.592);
        }
    }
}
