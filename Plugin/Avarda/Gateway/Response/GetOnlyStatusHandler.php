<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Plugin\Avarda\Gateway\Response;

use Avarda\Checkout3\Gateway\Response\GetOnlyStatusHandler as AvardaGetOnlyStatusHandler;
use Avarda\ShippingBroker\Model\Provider\Pool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Quote\Api\CartRepositoryInterface;

class GetOnlyStatusHandler
{
    protected CartRepositoryInterface $quoteRepository;
    protected Pool $providerPool;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Pool $providerPool
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->providerPool = $providerPool;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function afterHandle(
        AvardaGetOnlyStatusHandler $subject,
        $result,
        array $handlingSubject,
        array $response
    ) {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $order = $this->quoteRepository->get($paymentDO->getOrder()->getId());
        $parsedResponse = $this->providerPool->getActive()->getResponseParser()->parse($response);
        if (!$parsedResponse) {
            return;
        }
        $shippingAddress = $order->getShippingAddress();
        $rateId = $shippingAddress->getShippingRatesCollection()->getFirstItem()->getId();
        $rate = $shippingAddress->getShippingRateById((int) $rateId);
        if ($rate) {
            $rate->setMethodDescription(json_encode($parsedResponse));
            $rate->setPrice($parsedResponse['price'])->save();
        }
    }
}
