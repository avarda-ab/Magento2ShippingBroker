<?php

namespace Avarda\GatewayShipping\Plugin;

use Avarda\Checkout3\Controller\Checkout\Index;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SetShippingMethodPlugin
{
    protected CheckoutSession $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession,
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Index $subject
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeExecute(Index $subject)
    {
        $this->checkoutSession->getQuote()
            ->getShippingAddress()
            ->setShippingMethod('avarda_shipping_method_gateway')
            ->save();
    }
}
