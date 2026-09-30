<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Plugin\Avarda\Gateway\Response;

use Avarda\ShippingBroker\Api\Gateway\Response\ParserInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Avarda\Checkout3\Gateway\Response\GetOnlyStatusHandler as AvardaGetOnlyStatusHandler;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class GetOnlyStatusHandler
{
    public function __construct(
        protected readonly CartRepositoryInterface $quoteRepository,
        protected readonly ParserInterface $parser,
    ) {
    }

    /**
     * Shipping rates update after Handle request to Avarda
     *
     * @param AvardaGetOnlyStatusHandler $subject
     * @param mixed $result
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function afterHandle(
        AvardaGetOnlyStatusHandler $subject,
        $result,
        array $handlingSubject,
        array $response
    ) {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        // For an order payment the adapter id is the order entity id, not the quote id
        $quoteId = $payment instanceof OrderPaymentInterface
            ? $payment->getOrder()->getQuoteId()
            : $paymentDO->getOrder()->getId();
        if (!$quoteId) {
            return;
        }

        try {
            $quote = $this->quoteRepository->get((int) $quoteId);
        } catch (NoSuchEntityException $e) {
            // Quote of an old order may already be cleaned up; the status check itself must not fail because of it
            return;
        }
        $parsedResponse = $this->parser->parse($response);
        if (!$parsedResponse) {
            return;
        }
        $shippingAddress = $quote->getShippingAddress();
        $rateId = $shippingAddress->getShippingRatesCollection()->getFirstItem()->getId();
        $rate = $shippingAddress->getShippingRateById((int) $rateId);
        if ($rate) {
            $rate->setMethodDescription(json_encode($parsedResponse));
            $rate->setPrice($parsedResponse['price'])->save();
        }
    }
}
