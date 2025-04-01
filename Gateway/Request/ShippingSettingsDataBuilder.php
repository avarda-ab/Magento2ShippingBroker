<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Avarda\ShippingBroker\Api\Gateway\Request\CustomAttributeBuilderInterface;

class ShippingSettingsDataBuilder implements BuilderInterface
{
    public const SHIPPING_SETTINGS = 'shippingSettings';

    public const ATTRIBUTES = 'attributes';

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param array $customAttributesPool
     */
    public function __construct(
        protected readonly CartRepositoryInterface $quoteRepository,
        protected readonly array $customAttributesPool,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        if (0 == count($this->customAttributesPool)) {
            return [];
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $this->quoteRepository->get($paymentDO->getOrder()->getId());

        return [
            self::SHIPPING_SETTINGS => [
                self::ATTRIBUTES => $this->getAttributes($order),
            ]
        ];
    }

    /**
     * Render Attributes
     *
     * @param mixed $order
     * @return array
     */
    private function getAttributes($order): array
    {
        $customAttributes = [];
        /** @var CustomAttributeBuilderInterface $customAttributeBuilder */
        foreach ($this->customAttributesPool as $customAttributeBuilder) {
            $customAttributes[] = $customAttributeBuilder->build($order);
        }

        return $customAttributes;
    }
}
