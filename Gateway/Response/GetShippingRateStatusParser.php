<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Response;

use Avarda\ShippingBroker\Api\Gateway\Response\ParserInterface;

/**
 * Parse Gateway response regarding shipping rates
 */
class GetShippingRateStatusParser implements ParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(array $response): array | bool
    {
        if (isset($response['Modules']) && count($response['Modules']) > 0) {
            if (!isset($response['Modules'][0]['ShippingModule'])) {
                return false;
            }
            foreach ($response['Modules'] as $module) {
                $selectedShippingOption = $module['SelectedShippingOption'];
                if (!$selectedShippingOption) {
                    return $this->getLowestValueShippingData($module);
                } else {
                    return $this->getSelectedShippingData($selectedShippingOption, $module);
                }
            }
        }

        return false;
    }

    /**
     * Retrieve selected shipping method information
     *
     * @param array $selectedShippingOption
     * @param array $module
     * @return array|bool
     */
    private function getSelectedShippingData(array $selectedShippingOption, array $module): array | bool
    {
        $selectedOptionName = $selectedShippingOption['SelectedOptionName'];
        $selectedAgentId = $selectedShippingOption['SelectedAgentId'];
        $price = $selectedShippingOption['Price'];
        $optionIds = $selectedShippingOption['OptionIds'];
        $optionId = $optionIds[0];

        $widgetDataJson = $module['WidgetDataJson'];
        $widgetOption = [];
        foreach ($widgetDataJson['options'] ?? [] as $option) {
            if ($optionId == $option['id']) {
                $widgetOption = $option;
                break;
            }
        }
        if (!$widgetOption) {
            return false;
        }
        $carrierId = $widgetOption['carrierId'];
        $priceValue = $widgetOption['priceValue'];
        $defaultPrice = $widgetOption['defaultPrice'];
        $taxRate = $widgetOption['taxRate'];
        $serviceId = $widgetOption['serviceId'];

        $widgetAgent = [];
        foreach ($widgetOption['agents'] as $agent) {
            if (isset($agent['id']) && $selectedAgentId == $agent['id']) {
                $widgetAgent = $agent;
                break;
            }
        }

        return [
            'selectedOptionName' => $selectedOptionName,
            'selectedAgentId' => $selectedAgentId,
            'price' => $price,
            'optionId' => $optionId,
            'carrierId' => $carrierId,
            'priceValue' => $priceValue,
            'defaultPrice' => $defaultPrice,
            'taxRate' => $taxRate,
            'serviceId' => $serviceId,
            'widgetAgent' => $widgetAgent,
        ];
    }

    /**
     * Retrieve selected shipping method information
     *
     * @param array $module
     * @return array|bool
     */
    private function getLowestValueShippingData(array $module): array | bool
    {
        $widgetDataJson = $module['WidgetDataJson'];
        $widgetOption = [];
        $minPrice = null;
        foreach ($widgetDataJson['options'] ?? [] as $option) {
            if (null === $minPrice || $minPrice > $option['priceValue']) {
                $widgetOption = $option;
                $minPrice = $option['priceValue'];
            }
        }
        if (!$widgetOption) {
            return false;
        }
        $carrierId = $widgetOption['carrierId'];
        $priceValue = $widgetOption['priceValue'];
        $defaultPrice = $widgetOption['defaultPrice'];
        $taxRate = $widgetOption['taxRate'];
        $serviceId = $widgetOption['serviceId'];

        return [
            'selectedOptionName' => null,
            'selectedAgentId' => null,
            'price' => $priceValue,
            'optionId' => $widgetOption['id'],
            'carrierId' => $carrierId,
            'priceValue' => $priceValue,
            'defaultPrice' => $defaultPrice,
            'taxRate' => $taxRate,
            'serviceId' => $serviceId,
            'widgetAgent' => null,
        ];
    }
}
