<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Model\Carrier;

use Avarda\Checkout3\Api\QuotePaymentManagementInterface;
use Avarda\Checkout3\Helper\PaymentData;
use Avarda\ShippingBroker\Model\Provider\Pool;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Xml\Security;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Psr\Log\LoggerInterface;

class Avarda extends AbstractCarrierOnline implements CarrierInterface
{
    public const METHOD_CODE = 'shipping_broker';
    protected $_code = 'avarda';
    protected $_isFixed = true;

    protected ClientInterface $httpClient;
    protected Pool $providerPool;
    protected PaymentData $paymentDataHelper;
    protected RedirectInterface $redirect;
    protected Session $checkoutSession;
    protected TransferFactoryInterface $transferFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        ResultFactory $trackFactory,
        ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        ClientInterface $httpClient,
        Pool $providerPool,
        PaymentData $paymentDataHelper,
        RedirectInterface $redirect,
        Session $checkoutSession,
        TransferFactoryInterface $transferFactory,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->httpClient = $httpClient;
        $this->providerPool = $providerPool;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->redirect = $redirect;
        $this->checkoutSession = $checkoutSession;
        $this->transferFactory = $transferFactory;
    }

    public function collectRates(RateRequest $request)
    {
        $result = $this->_rateFactory->create();
        if (!$this->getConfigFlag('active')) {
            return $result;
        }

        $method = $this->createResultMethod($request);
        if (!str_contains($this->redirect->getRefererUrl(), 'avarda3/checkout') &&
            !$method->getData('price')
        ) {
            return $result;
        }

        $result->append($method);

        return $result;
    }

    protected function _doShipmentRequest(DataObject $request)
    {
        return $request;
    }

    public function getAllowedMethods()
    {
        return [
            self::METHOD_CODE => $this->getConfigData('name')
        ];
    }

    /**
     * @return Method
     */
    private function createResultMethod($request)
    {
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setMethod(self::METHOD_CODE);

        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingStatus = $this->getAvardaStatus($request);
        if ($shippingStatus) {
            $method->setMethodTitle($shippingStatus['selectedOptionName'] ?? '');
            $method->setPrice($shippingStatus['price']);
            $method->setMethodDescription(json_encode($shippingStatus));
        }

        return $method;
    }

    /**
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function getAvardaStatus($request): bool|array
    {
        if (!$request->getAllItems()) {
            return false;
        }

        // By taking the quote from the item we avoid collecting totals which could end up in infinite loop
        $firstItem = $request->getAllItems()[0];
        /** @var CartInterface $quote */
        $quote = $firstItem->getQuote();

        $purchaseData = $this->paymentDataHelper->getPurchaseData(
            $quote->getPayment()
        );

        if (!$purchaseData || count($purchaseData) == 0) {
            return false;
        }

        /** @TODO: change to 'additional' builder */
        $transfer = $this->transferFactory->create([
            "additional" => [
                'purchaseid' => $purchaseData['purchaseId'],
                'storeId' => $quote->getStoreId(),
                'useAltApi' => false
            ]
        ]);
        return $this->providerPool->getActive()->getResponseParser()
            ->parse($this->httpClient->placeRequest($transfer));
    }

    protected function getQuote()
    {
        if (!isset($this->quote)) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    public function processAdditionalValidation(DataObject $request)
    {
        return true;
    }
}
