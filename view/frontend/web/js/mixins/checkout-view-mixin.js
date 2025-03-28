/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_GatewayShipping
 */
define([], function () {
    'use strict';

    // Change payment method title for now set to empty string
    let mixin = {
        getPaymentStepTitle: function () {
            return "";
        },
    };
    return function (target) {
        return target.extend(mixin);
    };
});
