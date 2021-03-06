/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        "underscore",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service',
        'mage/validation',
        'Magento_Ui/js/form/element/region'
    ],
    function ($,
              _,
              Component,
              ko,
              customer,
              addressList,
              addressConverter,
              quote,
              createShippingAddress,
              selectShippingAddress,
              shippingRatesValidator,
              formPopUpState,
              shippingService,
              selectShippingMethodAction,
              rateRegistry,
              setShippingInformationAction,
              stepNavigator,
              modal,
              checkoutDataResolver,
              checkoutData,
              registry,
              $t,
              region) {
        'use strict';

        var popUp = null,
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping'
            },
            isVisible: function () {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#shipping');
                return element.hasClass('active');
            },
            self: this,
            visible: ko.observable(!quote.isVirtual()),
            errorValidationMessage: ko.observable(false),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: true,
            quoteIsVirtual: quote.isVirtual(),

            initialize: function () {
                var self = this;
                this._super();

                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'shipping',
                    //step alias
                    null,
                    //step title value
                    'Shipping',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),
                    16
                );

                checkoutDataResolver.resolveShippingAddress();

                var hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                this.isFormPopUpVisible.subscribe(function (value) {
                    if (value) {
                        self.getPopUp().openModal();
                    }
                });


                quote.shippingMethod.subscribe(function (value) {
                    self.errorValidationMessage(false);
                });

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();
                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                });
                return this;
            },

            navigate: function () {
                //vary bad code starts here. lack of time, nuff said
                var country = document.getElementsByName('country_id');
                var input = document.getElementsByName('shippingAddress.region');
                $(input[0]).hide();
                var selectedCountry = $(country[0]);
                this.resolveCountry();
                var shipping = this;
                selectedCountry.change(function () {
                    shipping.resolveCountry(selectedCountry, input);
                });
            },
            initChildren: function () {
                console.log('init children');
            },
            initElement: function (element) {
                if (element.index === 'shipping-address-fieldset') {
                    shippingRatesValidator.bindChangeHandlers(element.elems(), false);

                }
            },

            resolveCountry: function (selectedCountry, input) {
                if (selectedCountry === undefined) {
                    var country = document.getElementsByName('country_id');
                    var selectedCountry = $(country[0]);
                }

                if (input  === undefined) {
                    var input = document.getElementsByName('shippingAddress.region');
                }
                $(input[0]).hide();

                var postcode = document.getElementsByName('shippingAddress.postcode');
                var key = selectedCountry.find("option:selected").val();
                console.log(key);
                console.log(window.countriesWithOptionalZip);
                if ($.inArray(key, window.countriesWithOptionalZip) >= 0) {
                    $(postcode[0]).hide();
                } else {
                    $(postcode[0]).show();
                }
            },

            getPopUp: function () {
                var self = this;
                if (!popUp) {
                    var buttons = this.popUpForm.options.buttons;
                    this.popUpForm.options.buttons = [
                        {
                            text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                            class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                            click: self.saveNewAddress.bind(self)
                        },
                        {
                            text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                            class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                            click: function () {
                                this.closeModal();
                            }
                        }
                    ];
                    this.popUpForm.options.closed = function () {
                        self.isFormPopUpVisible(false);
                    };
                    popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
                }
                return popUp;
            },

            /** Show address form popup */
            showFormPopUp: function () {
                this.isFormPopUpVisible(true);
            },


            /** Save new shipping address */
            saveNewAddress: function () {
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    var addressData = this.source.get('shippingAddress');
                    addressData.save_in_address_book = this.saveInAddressBook;

                    // New address must be selected as a shipping address
                    var newShippingAddress = createShippingAddress(addressData);
                    selectShippingAddress(newShippingAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);
                    this.getPopUp().closeModal();
                    this.isNewAddressAdded(true);
                }
            },

            /** Shipping Method View **/
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod()
                        ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),

            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
                return true;
            },

            setShippingInformation: function () {
                if (this.validateShippingInformation()) {

                    //setShippingInformationAction().done(
                    //    function () {
                    var addr = checkoutData.getShippingAddressFromData();
                    var addrData = addr.firstname + ' '
                        + addr.lastname + ' '
                        + addr.street[0] + ' '
                        + addr.street[1] + ' ' + addr.city + ','
                        + addr.postcode + ' ' + addr.country_id
                        + 'Telephone: ' + addr.telephone;
                    $('#shipping-information-summary').find('.a-step-result p').text(addrData);
                    stepNavigator.next();
                    //}
                    //);
                } else {
                    console.log('valid fails');
                }
            },

            validateShippingInformation: function () {

                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                //if (!quote.shippingMethod()) {
                //    this.errorValidationMessage('Please specify a shipping method');
                //    return false;
                //}

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();
                }
                //if (this.isFormInline) {

                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');
                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if ($('#checkout-step-shipping').find('.mage-error:visible').length > 0) {

                        return false;
                    }
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
                //}

                return true;
            },
            navigateToNextStep: function () {

                this.setShippingInformation();
            },
            navigateToPrevStep: function () {
                if (!customer.isLoggedIn()) {

                    stepNavigator.navigateTo('authentication');
                } else {

                    stepNavigator.prev();
                }

            },
            validateForm: function (form) {

                return $(form).validation() && $(form).validation('isValid');
            },
            showIfUserIsLogged: function () {
                return customer.isLoggedIn() && !addressOptions.length > 0;
            }
        });
    }
);
