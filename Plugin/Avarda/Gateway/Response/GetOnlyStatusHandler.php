<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\GatewayShipping\Plugin\Avarda\Gateway\Response;

use Avarda\GatewayShipping\Api\Gateway\Response\ParserInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Avarda\Checkout3\Gateway\Response\GetOnlyStatusHandler as AvardaGetOnlyStatusHandler;
use Magento\Quote\Api\CartRepositoryInterface;

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
        $parsedResponse = $this->parser->parse($response);
        if (!$parsedResponse) {
            return;
        }
        $shippingAddress = $order->getShippingAddress();
        $rateId = $shippingAddress->getShippingRatesCollection()->getFirstItem()->getId();
        $rate = $shippingAddress->getShippingRateById((int) $rateId);
        if ($rate != false) {
            $rate->setMethodDescription(json_encode($parsedResponse));
            $rate->setPrice($parsedResponse['price'])->save();
        }
    }
}
