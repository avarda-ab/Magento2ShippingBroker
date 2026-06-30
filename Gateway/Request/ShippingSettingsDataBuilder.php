<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Gateway\Request;

use Avarda\ShippingBroker\Api\Gateway\Request\CustomAttributeBuilderInterface;
use Avarda\ShippingBroker\Model\Provider\Pool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class ShippingSettingsDataBuilder implements BuilderInterface
{
    public const SHIPPING_SETTINGS = 'shippingSettings';

    public const ATTRIBUTES = 'attributes';

    protected CartRepositoryInterface $quoteRepository;
    protected Pool $providerPool;
    protected LoggerInterface $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Pool $providerPool,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->providerPool = $providerPool;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $customAttributesPool = $this->getCustomAttributesPool();
        if (count($customAttributesPool) === 0) {
            return [];
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $this->quoteRepository->get($paymentDO->getOrder()->getId());

        if ($order->getIsVirtual()) {
            return [];
        }

        return [
            self::SHIPPING_SETTINGS => [
                self::ATTRIBUTES => $this->getAttributes($order, $customAttributesPool),
            ]
        ];
    }

    /**
     * Build attribute strings via the active provider's pool.
     *
     * @param CustomAttributeBuilderInterface[] $customAttributesPool
     * @return string[]
     */
    private function getAttributes($order, array $customAttributesPool): array
    {
        $customAttributes = [];
        foreach ($customAttributesPool as $customAttributeBuilder) {
            $customAttributes[] = $customAttributeBuilder->build($order);
        }

        return $customAttributes;
    }

    /**
     * @return CustomAttributeBuilderInterface[]
     */
    private function getCustomAttributesPool(): array
    {
        try {
            return $this->providerPool->getActive()->getCustomAttributesPool();
        } catch (LocalizedException $e) {
            $this->logger->warning(
                'Avarda ShippingBroker: cannot resolve active provider for shipping settings.',
                ['exception' => $e]
            );
            return [];
        }
    }
}
