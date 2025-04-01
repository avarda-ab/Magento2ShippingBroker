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
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Session $session
     * @param UrlInterface|null $url
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        protected readonly Session $session,
        UrlInterface $url = null
    ) {
        parent::__construct($context, $storeManager, $url);
    }

    /**
     * @var string[]
     */
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

    /**
     * Retrieve shipping rate details
     *
     * @param Order $order
     * @return array
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

    /**
     * Prepare shipping rate details
     *
     * @param array $details
     * @return array
     */
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
