/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
    ],
    function (Component, priceUtils, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/item/details'
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            },
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
        });
    }
);
