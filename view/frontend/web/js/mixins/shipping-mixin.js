/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_GatewayShipping
 */
define([], function () {
    'use strict';

    // Do not show shipping title in summary
    let mixin = {
        getShippingMethodTitle: function () {
            return "";
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
