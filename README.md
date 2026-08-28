# Avarda_ShippingBroker

Provider-agnostic core for Avarda Checkout3 shipping. Replaces Magento's
shipping step with a broker carrier and resolves the active shipping provider
per store scope.

Install one provider module alongside it:

- [Avarda_ShippingBrokerNshift](https://github.com/avarda-ab/Magento2ShippingBrokerNshift) — nShift / Unifaun
- [Avarda_ShippingBrokerPartner](https://github.com/avarda-ab/Magento2ShippingBrokerPartner) — Magento as Partner Shipping implementor

## Usage

Requires `avarda/checkout3`.

```
bin/magento module:enable Avarda_ShippingBroker
bin/magento setup:upgrade
```

Enable a provider module, then pick it at **Stores → Configuration → Sales →
Payment Methods → Avarda Checkout V3 → Avarda Shipping Broker → Provider**.
