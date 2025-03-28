/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Checkout3
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/shipping': {
                'Avarda_GatewayShipping/js/mixins/shipping-mixin': true
            },
            'Avarda_Checkout3/js/view/checkout-view': {
                'Avarda_GatewayShipping/js/mixins/checkout-view-mixin': true
            },
            'Avarda_Checkout3/js/view/shipping-method': {
                'Avarda_GatewayShipping/js/mixins/shipping-method-mixin': true
            }
        }
    }
};
