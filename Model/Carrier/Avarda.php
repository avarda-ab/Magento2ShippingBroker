<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Model\Carrier;

use Avarda\Checkout3\Api\QuotePaymentManagementInterface;
use Avarda\ShippingBroker\Api\Gateway\Response\ParserInterface;
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
use Magento\Quote\Model\Quote\Address\RateRequest;
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
    public const string METHOD_CODE = 'shipping_broker';
    protected $_code = 'avarda';
    protected $_isFixed = true;

    protected ClientInterface $httpClient;
    protected ParserInterface $parser;
    protected QuotePaymentManagementInterface $quotePaymentManagement;
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
        ParserInterface $parser,
        QuotePaymentManagementInterface $quotePaymentManagement,
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
        $this->parser = $parser;
        $this->quotePaymentManagement = $quotePaymentManagement;
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

        $method = $this->createResultMethod();
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

    /**
     * Get allowed methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [
            self::METHOD_CODE => $this->getConfigData('name')
        ];
    }

    private function createResultMethod()
    {
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

    public function getAvardaStatus(): bool|array
    {
        $purchaseData = $this->quotePaymentManagement->getPurchaseData($this->getQuote()?->getId());
        /** @TODO: change to 'additional' builder */
        $transfer = $this->transferFactory->create([
            "additional" => [
                'purchaseid' => $purchaseData['purchaseId'],
                'storeId' => $this->getQuote()->getStoreId(),
                'useAltApi' => false
            ]
        ]);
        return $this->parser->parse($this->httpClient->placeRequest($transfer));
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
