<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Model\Carrier;

use Avarda\Checkout3\Api\QuotePaymentManagementInterface;
use Avarda\ShippingBroker\Api\Gateway\Response\ParserInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Http\AvardaerException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * The Avarda Shipping Carrier for our shipping api gateway
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
class Avarda extends AbstractCarrier implements CarrierInterface
{
    public const METHOD_CODE = 'shipping_broker';

    /**
     * @var string $_code
     */
    protected $_code = 'avarda';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected MethodFactory $_rateMethodFactory;

    /**
     * @var CartInterface
     */
    protected CartInterface $quote;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        protected readonly QuotePaymentManagementInterface $quotePaymentManagement,
        protected readonly Session $checkoutSession,
        protected readonly ClientInterface $httpClient,
        protected readonly TransferFactoryInterface $transferFactory,
        protected readonly ParserInterface $parser,
        public RedirectInterface $redirect,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result
     * @throws ClientException
     */
    public function collectRates(RateRequest $request): Result
    {
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        if (!$this->getConfigFlag('active')) {
            return $result;
        }

        $method = $this->createResultMethod();
        if (!str_contains($this->redirect->getRefererUrl(), 'avarda3/checkout') && !$method->getData('price')) {
            return $result;
        }

        $result->append($method);

        return $result;
    }

    /**
     * Retrieve shipping price by gateway request
     *
     * @return bool|array
     * @throws ClientException
     * @throws AvardaerException
     */
    public function getAvardaStatus(): bool|array
    {
        $purchaseData = $this->quotePaymentManagement->getPurchaseData($this->getQuote()?->getId());
        /** @TODO: change to 'additional' builder */
        $transferO = $this->transferFactory->create([
            "additional" => [
                'purchaseid' => $purchaseData['purchaseId'],
                'storeId' => $this->getQuote()->getStoreId(),
                'useAltApi' => false
            ]
        ]);
        return $this->parser->parse($this->httpClient->placeRequest($transferO));
    }

    /**
     * Return unchanged input object because we're not having a pure carrier web service
     *
     * @param DataObject $request
     * @return DataObject
     * @codeCoverageIgnore
     * @codingStandardsIgnoreLine
     */
    public function _doShipmentRequest(DataObject $request): DataObject
    {
        return $request;
    }

    /**
     * @inheritDoc
     */
    public function getAllowedMethods(): array
    {
        return [self::METHOD_CODE => $this->getConfigData('name')];
    }

    /**
     * Create rate object based on shipping price.
     *
     * @return Method
     * @throws ClientException
     * @throws AvardaerException
     */
    private function createResultMethod(): Method
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setMethod(self::METHOD_CODE);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingStatus = $this->getAvardaStatus();
        if ($shippingStatus) {
            $method->setMethodTitle($shippingStatus['selectedOptionName'] ?? '');
            $method->setPrice($shippingStatus['price']);
            $method->setMethodDescription(json_encode($shippingStatus));
        }

        return $method;
    }

    /**
     * Retrieve quote object
     *
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote(): CartInterface
    {
        if (!isset($this->quote)) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}
