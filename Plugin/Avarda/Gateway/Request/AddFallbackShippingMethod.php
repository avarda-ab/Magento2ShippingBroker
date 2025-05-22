<?php

namespace Avarda\ShippingBroker\Plugin\Avarda\Gateway\Request;

use Avarda\Checkout3\Gateway\Request\ItemsDataBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddFallbackShippingMethod
{
    const string FALLBACK_SHIPPING_CODE = 'SHI001';

    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add default shipping item based on the configuration
     *
     * TODO: Add a config for Avarda which is selection of Magento's shipping methods
     *
     * Documentation: https://docs.avarda.com/checkout-3/shipping-broker/common-integration-guide/default-shipping-item/
     *
     * @param ItemsDataBuilder $subject
     * @param array $result
     * @return array
     */
    public function afterBuild(
        ItemsDataBuilder $subject,
        array $result
    ) {
        /*$result[ItemsDataBuilder::ITEMS][] = (object) [
            'Description' => 'Shipping Fallback - (name)',
            'Notes' => self::FALLBACK_SHIPPING_CODE,
            'Amount' => 5.0,
            'TaxCode' => '25.5%',
            'TaxAmount' => 25.5,
            'Quantity' => 1,
        ];*/

        return $result;
    }
}
