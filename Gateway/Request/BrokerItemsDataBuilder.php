<?php

namespace Avarda\ShippingBroker\Gateway\Request;

use Avarda\Checkout3\Api\ItemStorageInterface;
use Avarda\Checkout3\Gateway\Request\ItemsDataBuilder;
use Magento\Payment\Gateway\Request\BuilderInterface;

class BrokerItemsDataBuilder extends ItemsDataBuilder
{
    const string FALLBACK_SHIPPING_CODE = 'SHI001';

    public function __construct(
        ItemStorageInterface $itemStorage,
        BuilderInterface $itemBuilder
    ) {
        parent::__construct($itemStorage, $itemBuilder);
    }

    public function build(array $buildSubject)
    {
        $result = parent::build($buildSubject);

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
}
