<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request\Item;

use Avarda\Checkout3\Gateway\Data\ItemAdapter\QuoteItem;
use Avarda\Checkout3\Gateway\Data\ItemAdapterInterface;
use Avarda\Checkout3\Gateway\Helper\ItemSubjectReader;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;

class ShippingParametersDataBuilder implements BuilderInterface
{
    public const SHIPPING_PARAMETERS = 'shippingParameters';

    public const WEIGHT = 'weight';

    protected ProductRepositoryInterface $productRepository;
    protected StoreConfigManagerInterface $storeConfigManager;
    protected ?StoreConfigInterface $storeConfig = null;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreConfigManagerInterface $storeConfigManager,
    ) {
        $this->productRepository = $productRepository;
        $this->storeConfigManager = $storeConfigManager;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $item = ItemSubjectReader::readItem($buildSubject);
        $qty = (int) ItemSubjectReader::readQty($buildSubject);
        $weight = $this->getItemWeight($item);
        if ($weight === null) {
            return [];
        }

        return [
            'articleNumber' => mb_substr((string) $item->getSku(), 0, 35),
            self::SHIPPING_PARAMETERS => [
                self::WEIGHT => $this->getWeightInGrams($weight) * $qty,
            ],
        ];
    }

    public function getItemWeight(ItemAdapterInterface $item): ?float
    {
        // Custom option SKUs are appended to the quote item SKU, so a product lookup by that SKU fails
        if ($item instanceof QuoteItem) {
            return (float) $item->getQuoteItem()->getWeight();
        }

        try {
            return (float) $this->productRepository->get((string) $item->getSku())->getWeight();
        } catch (LocalizedException $e) {
            return null;
        }
    }

    public function getStoreConfig(): StoreConfigInterface
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
