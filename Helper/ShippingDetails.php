<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Model\Order;
use Magento\Shipping\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class ShippingDetails extends Data
{
    protected Session $session;
    protected array $shippingDetailKeys = [
        'name',
        'address1',
        'address2',
        'zipCode',
        'city',
        'mapLongitude',
        'mapLatitude',
        'phone',
        'email'
    ];

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Session $session,
        UrlInterface $url = null
    ) {
        parent::__construct($context, $storeManager, $url);
        $this->session = $session;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getShippingRateDetails(Order $order): array
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->session->getQuote()->load($quoteId);
        $address = $quote->getShippingAddress();
        $address->collectShippingRates();
        $rates = $address->getGroupedAllShippingRates();

        if (!empty($rates) && isset($rates['avarda_shipping_broker'])) {
            /** @var Rate $rate */
            $rate = array_pop($rates['avarda_shipping_broker']);
            $details = json_decode($rate->getMethodDescription() ?? '', true);
            return $this->parseShippingRateDetails($details ?? []);
        }

        return [];
    }

    public function parseShippingRateDetails(array $details): array
    {
        $parsedShippingDetails = [];
        foreach ($details['widgetAgent'] ?? [] as $key => $value) {
            if (in_array($key, $this->shippingDetailKeys) && null !== $value) {
                $parsedShippingDetails[strtoupper($key)] = $value;
            }
        }
        return $parsedShippingDetails;
    }
}
