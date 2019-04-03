/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (ko, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/buttons'
            }
        });
    }
);