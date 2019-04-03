define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        quote,
        $,
        paymentService,
        methodConverter,
        getPaymentInformation,
        checkoutDataResolver
    ) {
        'use strict';
        /** Set payment methods to collection */

        paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));
        /**
         *
         * mystep - is the name of the component's .html template,
         * your_module_dir - is the name of the your module directory.
         *
         */
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment-information',
                activeMethod: ''
            },
            errorValidationMessage: ko.observable(false),
            quoteIsVirtual: quote.isVirtual(),

            //add here your logic to display step,
            //isVisible: ko.observable(true),
            isVisible: function() {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#payment_information');
                return element.hasClass('active');
            },
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                // register your step
                checkoutDataResolver.resolvePaymentMethod();
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'payment_information',
                    //step alias
                    null,
                    //step title value
                    'Payment Information',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    19
                );
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
                var form = $('#co-payment-form');
                var checkedMethod = form.find("input[type='radio']:checked");
                if (form.find('.mage-error').length <= 0){
                    form.find('.mage-error').remove();
                }
                if (checkedMethod.length <= 0) {
                    var errorMessage = '<div class="mage-error">' + error + '</div>';
                    $(errorMessage).insertAfter(form);
                } else {
                    var method = quote.paymentMethod();
                    var paymentData = 'Method:  '  + method.title;
                    $('#payment-method-summary').find('.a-step-result p').text(paymentData);
                    //stepNavigator.next();
                }
            },
            navigateToPrevStep: function () {
                stepNavigator.prev();
            },
            getFormKey: function() {
                return window.checkoutConfig.formKey;
            },
            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                console.log('placeOrder');

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
            }
        });
    }
);