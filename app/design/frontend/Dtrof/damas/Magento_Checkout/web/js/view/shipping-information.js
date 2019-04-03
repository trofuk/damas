/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
//define(
//    [
//        'jquery',
//        'uiComponent',
//        'Magento_Checkout/js/model/quote',
//        'Magento_Checkout/js/model/step-navigator',
//        'Magento_Checkout/js/model/sidebar'
//    ],
//    function($, Component, quote, stepNavigator, sidebarModel) {
//        'use strict';
//        return Component.extend({
//            defaults: {
//                template: 'Magento_Checkout/shipping-information'
//            },
//
//            isVisible: function() {
//                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
//            },
//
//            getShippingMethodTitle: function() {
//                var shippingMethod = quote.shippingMethod();
//                return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title : '';
//            },
//
//            back: function() {
//                sidebarModel.hide();
//                stepNavigator.navigateTo('shipping');
//            },
//
//            backToShippingMethod: function() {
//                sidebarModel.hide();
//                stepNavigator.navigateTo('shipping', 'opc-shipping_method');
//            }
//        });
//    }
//);
define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function (
        ko,
        Component,
        _,
        addressList,
        stepNavigator,
        quote,
        $,
        setShippingInformationAction,
        customer,
        shippingService
    ) {
        'use strict';



        return Component.extend({

            errorValidationMessage: ko.observable(false),

            /** Shipping Method View **/
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod()
                        ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),


            //add here your logic to display step,
            //isVisible: ko.observable(true),
            setShippingInformation: function () {
                console.log('set shipping info!!!!');
                if (this.validateShippingInformation()) {
                    console.log('validateShippingInformation!!!');
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            isFormInline: addressList().length == 0,
            saveInAddressBook: true,
            //add here your logic to display step,
                isVisible: function() {
                    //return !quote.isVirtual() && stepNavigator.isProcessed('shipping_information');
                    //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                    var element = $('#shipping_information');
                    return element.hasClass('active');
                },

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();


                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {

            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                this.setShippingInformation();

            },
            navigateToPrevStep: function () {
                if (!customer.isLoggedIn()) {
                    console.log('not logged!!!');
                    stepNavigator.navigateTo('authentication');
                }
                console.log('logged!!!');
                stepNavigator.prev();
            },
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method');
                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');
                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }
                    ;

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {
                        if (addressData.hasOwnProperty(field)
                            && shippingAddress.hasOwnProperty(field)
                            && typeof addressData[field] != 'function'
                        ) {
                            shippingAddress[field] = addressData[field];
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = true;
                    }
                    selectShippingAddress(shippingAddress);
                }
                return true;
            }
        });
    }
);
