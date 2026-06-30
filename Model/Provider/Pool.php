<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Model\Provider;

use Avarda\ShippingBroker\Api\ProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Registry of shipping broker providers, resolves the active one
 * for the current store scope.
 */
class Pool
{
    public const CONFIG_PATH_PROVIDER = 'carriers/avarda/provider';

    // Literal default; the nShift provider now ships in a separate module.
    public const DEFAULT_PROVIDER = 'nshift';

    protected ScopeConfigInterface $scopeConfig;
    protected array $providers;

    /**
     * @param ProviderInterface[] $providers Keyed by provider code; injected via di.xml.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $providers
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->providers = $providers;
    }

    /**
     * Resolve the provider configured for the current store.
     *
     * @throws LocalizedException
     */
    public function getActive(): ProviderInterface
    {
        $code = (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PROVIDER,
            ScopeInterface::SCOPE_STORE
        );
        if ($code === '') {
            $code = self::DEFAULT_PROVIDER;
        }
        return $this->get($code);
    }

    /**
     * @throws LocalizedException
     */
    public function get(string $code): ProviderInterface
    {
        if (!isset($this->providers[$code])) {
            throw new LocalizedException(
                __('Unknown Avarda shipping broker provider: %1', $code)
            );
        }
        return $this->providers[$code];
    }

    /**
     * @return ProviderInterface[]
     */
    public function getAll(): array
    {
        return $this->providers;
    }
}
