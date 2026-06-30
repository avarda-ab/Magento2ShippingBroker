<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Model\Config\Source;

use Avarda\ShippingBroker\Model\Provider\Pool;
use Magento\Framework\Data\OptionSourceInterface;

class Provider implements OptionSourceInterface
{
    protected Pool $providerPool;

    public function __construct(
        Pool $providerPool
    ) {
        $this->providerPool = $providerPool;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->providerPool->getAll() as $provider) {
            $options[] = [
                'value' => $provider->getCode(),
                'label' => ucfirst($provider->getCode()),
            ];
        }
        return $options;
    }
}
