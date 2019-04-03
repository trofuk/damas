/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        '../model/quote',
        'Magento_Customer/js/model/address-list'

    ],
    function ($, quote, addressList) {
        'use strict';

        return function (billingAddress) {
            var address = null;

            if (typeof billingAddress === 'string') {
                var selectedAddressObject = addressList().filter(function (address) {
                    return address.getAddressInline() == billingAddress;
                });
                billingAddress = selectedAddressObject[0];
            }

            if (quote.shippingAddress() && billingAddress.getCacheKey() == quote.shippingAddress().getCacheKey()) {
                address = $.extend({}, billingAddress);
                address.saveInAddressBook = false;
            } else {
                address = billingAddress;
            }
            quote.billingAddress(address);
        };
    }
);
