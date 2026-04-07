/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_ShippingBroker
 */
define([], function () {
    'use strict';

    // Do not show the shipping title in summary
    let mixin = {
        getShippingMethodTitle: function () {
            return "";
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
