/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_GatewayShipping
 */
define([
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/view/shipping',
], function (
    checkoutDataResolver,
    grandParentComponent,
) {
    'use strict';

    // Change payment method title for now set to empty string
    let mixin = {
        initialize: function () {
            let self = this;
            checkoutDataResolver.resolveBillingAddress();
            checkoutDataResolver.resolveShippingAddress();
            self.initializeIframe();
            // Call grandparent init and skip parent (=avarda shipping method) init
            grandParentComponent.prototype.initialize.call(this);
        },
        avardaCheckoutInitOptions: function (options) {
            let self = this;
            options.shippingOptionChangedCallback = function({ price, currency }, checkoutInstance) {
                self.selectShippingMethod({'carrier_code': 'avarda_shipping_method_gateway', 'method_code': 'avarda_shipping_method_gateway'});
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
