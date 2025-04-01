# Avarda ShippingBroker

Avarda shipping broker handling for Avarda Checkout3.
The main goal of this module is implementation of the shipping broker API on example of the Nshift broker.

[See provider-specific integration guide](https://docs.avarda.com/checkout-3/shipping-broker/provider-specific-integration-guide/)


## Features

Below there is a list of features on implemented version 1.0.0

1. Handle *rates collection* as a seamless integration with the shipping broker that replaces magento shipping methods. Rates are obligatory to place an order in magento.

2. Handle Nshift custom attributes:
    - discount
    - weight
    - free shipping

    Possible scenarios:
    - send free shipping flag based on pricing rules used in magento
    - use discount calculated in magento
    - send weight parameter to let it be stored or used by the shipping broker 

3. Display details of the shipping details in magento admin.
4. Fix enabling the nshift script usage

## TODO
1. Allow enabling/disabling the solution from the admin panel.


## USAGE
Assuming:
1. avarda/checkout3 is installed and configured
2. shipping broker credentials is added your Avarda account
3. Delivery methods are configured on the nshift account

To install and use Avarda\ShippingBroker module in Magento:
1. Install the module i.e. with composer
```
composer require avarda/shipping-broker
bin/magento module:enable Avarda_ShippingBroker
bin/magento setup:upgrade
bin/magento setup:di:compile
```
2. Go to the checkout in your store to see the nshift delivery methods
3. To use the free shipping based on the cart - use cart price rule do define conditions for which free shipping should appear.
