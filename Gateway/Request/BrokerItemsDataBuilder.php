<?php

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request;

use Avarda\Checkout3\Api\ItemStorageInterface;
use Avarda\Checkout3\Gateway\Request\ItemsDataBuilder;
use Avarda\ShippingBroker\Model\Provider\Pool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Psr\Log\LoggerInterface;

class BrokerItemsDataBuilder extends ItemsDataBuilder
{
    public const FALLBACK_SHIPPING_CODE = 'SHI001';

    protected Pool $providerPool;
    protected LoggerInterface $logger;

    public function __construct(
        ItemStorageInterface $itemStorage,
        BuilderInterface $itemBuilder,
        Pool $providerPool,
        LoggerInterface $logger
    ) {
        parent::__construct($itemStorage, $itemBuilder);
        $this->providerPool = $providerPool;
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $result = parent::build($buildSubject);

        if (!$this->shouldInjectFallbackLine()) {
            return $result;
        }

        $result[ItemsDataBuilder::ITEMS][] = (object) [
            'Description' => 'Shipping Fallback',
            'Notes' => self::FALLBACK_SHIPPING_CODE,
            'Amount' => 0,
            'TaxCode' => '0',
            'TaxAmount' => 0,
            'Quantity' => 1,
        ];

        return $result;
    }

    private function shouldInjectFallbackLine(): bool
    {
        try {
            return $this->providerPool->getActive()->shouldInjectFallbackLine();
        } catch (LocalizedException $e) {
            $this->logger->warning(
                'Avarda ShippingBroker: cannot resolve active provider for items builder.',
                ['exception' => $e]
            );
            return false;
        }
    }
}
