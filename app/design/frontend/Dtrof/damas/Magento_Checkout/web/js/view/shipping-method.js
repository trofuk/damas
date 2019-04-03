define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-shipping-information',
        'mage/translate'

    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        quote,
        $,
        shippingService,
        selectShippingMethodAction,
        checkoutDataResolver,
        checkoutData,
        customer,
        setShippingInformationAction,
        $t

    ) {
        'use strict';
        /**
         *
         * mystep - is the name of the component's .html template,
         * your_module_dir - is the name of the your module directory.
         *
         */
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
            },
            errorValidationMessage: ko.observable(false),
            isVisible: function() {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#shipping_method');
                return element.hasClass('active');
            },
            freeShippingVisible: function() {
                if (this.rates().length == 1 && this.rates()[0].method_code == 'freeshipping') {
                    return false;
                } else {
                    return true;
                }

            },
            freeShippingIsNotVisible: function () {

                if(this.freeShippingVisible()) {
                    return false;
                }

                return true;
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

            //add here your logic to display step,
            //isVisible: ko.observable(true),
            setShippingInformation: function () {
                var form = $('#co-shipping-method-form');
                if (this.validateShippingInformation()) {
                    if (form.find('.mage-error').length <= 0){
                        form.find('.mage-error').remove();
                    }
                    setShippingInformationAction().done(
                        function () {
                            var method = quote.shippingMethod();
                            var addrData = 'Method:  '  + method.method_title + ', Carrier: ' + method.carrier_title ;
                            $('#shipping-information-method').find('.a-step-result p').text(addrData);
                            stepNavigator.next();
                        }
                    );
                } else {
                    var error = this.errorValidationMessage();

                    var checkedMethod = form.find("input[type='radio'][name='shipping_method']:checked");
                    if (checkedMethod.length <= 0) {
                        var errorMessage = '<div class="mage-error">' + error + '</div>';
                        $(errorMessage).insertAfter(form);
                        return false;
                    }
                }
            },
            /**
             *
             * @returns {*}
             */
            initialize: function () {
                var self = this;
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'shipping_method',
                    //step alias
                    null,
                    //step title value
                    'Shipping Method',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    17
                );

                quote.shippingMethod.subscribe(function (value) {
                    self.errorValidationMessage(false);
                });
                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {

            },
            initElement: function (element) {
                if (element.index === 'shipping-address-fieldset') {
                    shippingRatesValidator.bindChangeHandlers(element.elems(), false);
                }
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

                    if (this.source.get('params.invalid')
                        || !quote.shippingMethod().method_code
                        || !quote.shippingMethod().carrier_code
                        || !emailValidationResult
                    ) {
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
                }
                return true;
            },
            /**
             * @returns void
             */
            navigateToNextStep: function () {
                stepNavigator.next();
            },
            navigateToPrevStep: function () {
                stepNavigator.prev();
            }
        });
    }
);