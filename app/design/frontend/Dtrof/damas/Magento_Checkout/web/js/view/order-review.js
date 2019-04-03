define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'uiRegistry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Customer/js/model/customer'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        quote,
        $,
        additionalValidators,
        placeOrderAction,
        checkoutData,
        checkoutDataResolver,
        registry,
        setShippingInformationAction,
        customer
    ) {
        'use strict';
        /**
         *
         * mystep - is the name of the component's .html template,
         * your_module_dir - is the name of the your module directory.
         *
         */
        return Component.extend({
            redirectAfterPlaceOrder: true,
            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                //
            },

            defaults: {
                template: 'Magento_Checkout/order-review'
            },
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),
            //add here your logic to display step,
            //isVisible: ko.observable(true),
            isVisible: function() {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#order_review');
                return element.hasClass('active');
            },
            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                quote.billingAddress.subscribe(function(address) {
                    this.isPlaceOrderActionAllowed((address !== null));
                }, this);
                checkoutDataResolver.resolveBillingAddress();

                var billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {

                    var defaultAddressData = checkoutProvider.get(billingAddressCode);
                    if (defaultAddressData === undefined) {
                        // skip if payment does not have a billing address form
                        return;
                    }
                    var billingAddressData = checkoutData.getBillingAddressFromData();
                    if (billingAddressData) {
                        checkoutProvider.set(
                            billingAddressCode,
                            $.extend({}, defaultAddressData, billingAddressData)
                        );
                    }
                    checkoutProvider.on(billingAddressCode, function (billingAddressData) {
                        checkoutData.setBillingAddressFromData(billingAddressData);
                    }, billingAddressCode);
                });
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'order_review',
                    //step alias
                    null,
                    //step title value
                    'Order Review',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    18
                );

                return this;
            },

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
                //stepNavigator.next();
            },
            navigateToPrevStep: function () {
                stepNavigator.prev();
            },
            /**
             * Place order.
             */
            reviewPlaceOrder: function (data, event) {


                var self = this,
                    placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);


                    placeOrder = placeOrderAction(this.getData(), this.redirectAfterPlaceOrder, this.messageContainer);
                    console.log('placeOrdr!!!');
                    console.log(placeOrder);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },
            getCode: function () {
                var method = $('#payment_information').find("input[type='radio']:checked");
                return method.attr('id');
                //return this.item.method;
            },
            isChecked: ko.computed(function () {
                return quote.paymentMethod() ? quote.paymentMethod().method : null;
            }),

            validate: function () {
                return true;
            },
            /**
             * Get payment method data
             */
            getData: function() {
                return {
                    "method": quote.paymentMethod().method,
                    "po_number": null,
                    "additional_data": null
                };
            },
            disposeSubscriptions: function () {
                // dispose all active subscriptions
                var billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.off(billingAddressCode);
                });
            }

        });
    }
);