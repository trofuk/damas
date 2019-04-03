define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/set-billing-address',
        'select2',
        'Magento_Checkout/js/knockout-select2'

    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        quote,
        $,
        customer,
        addressList,
        customerData,
        checkoutDataResolver,
        checkoutData,
        selectShippingAddressAction,
        selectBillingAddress,
        select2,
        knockoutSelect2

    ) {
        'use strict';
        var lastSelectedBillingAddress = null,
            newAddressOption = {
                getAddressInline: function () {
                    //return $t('New Address');
                    return 'New Address';
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        //addressOptions.push(newAddressOption);
        /**
         *
         * mystep - is the name of the component's .html template,
         * your_module_dir - is the name of the your module directory.
         *
         */
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-information'
            },
            currentBillingAddress: quote.billingAddress,
            selectedAddress: quote.billingAddress,
            selectRendererCounter: 0,

            //add here your logic to display step,
            isVisible: function() {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#billing_information');
                return element.hasClass('active');
            },
            useThisAddress: ko.observable("1"),
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,


            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                quote.paymentMethod.subscribe(function () {
                    checkoutDataResolver.resolveBillingAddress();
                }, this);
                this.useThisAddress.subscribe(function(value){
                    $('#billing_information').find('.mage-error').remove();
                });
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'billing_information',
                    //step alias
                    null,
                    //step title value
                    'Billing Information',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    15
                );


                return this;
            },

            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.billingAddress() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: false,
                        saveInAddressBook: true
                    });

                quote.billingAddress.subscribe(function (newAddress) {
                    if (quote.isVirtual()) {
                        this.isAddressSameAsShipping(false);
                    } else {
                        true;
                        //console.log('quote');
                        //console.log(quote);
                        //this.isAddressSameAsShipping(
                        //    newAddress != null &&
                        //    newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                        //);
                    }

                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    }
                    this.isAddressDetailsVisible(true);
                }, this);

                return this;
            },

            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());
                    if (window.checkoutConfig.reloadOnBillingAddress) {
                        setBillingAddressAction(globalMessageList);
                    }
                    this.isAddressDetailsVisible(true);
                } else {
                    lastSelectedBillingAddress = quote.billingAddress();
                    quote.billingAddress(null);
                    this.isAddressDetailsVisible(false);
                }
                checkoutData.setSelectedBillingAddress(null);
                return true;
            },

            showIfUserIsLogged: function (){
                return customer.isLoggedIn() && addressOptions.length > 0;
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
                if (this.useThisAddress() == 1){
                    if (this.selectedAddress.countryId !== 'AE') {


                        var errorMessage = '<div class="mage-error">Sorry, we deliver only in UAE. Choose another country please</div>';
                        $(errorMessage).insertAfter($('select[name=billing_address_id]'));
                    } else{
                        var addrData = this.selectedAddress.firstname + ' '
                            + this.selectedAddress.lastname + ' ';
                        if (this.selectedAddress.street) {
                            addrData += this.selectedAddress.street[0] + ' '
                                + this.selectedAddress.street[1];
                        }
                        if (this.selectedAddress.city) {
                            addrData +=  ' ' + this.selectedAddress.city + ',';
                        }
                        if (this.selectedAddress.postcode) {
                            addrData +=  ' ' + this.selectedAddress.postcode + ' ';
                        }
                        if (this.selectedAddress.telephone) {
                            addrData +=   'Telephone: ' + this.selectedAddress.country_id + ' ';
                        }
                        $('#billing-information-summary').find('.a-step-result p').text(addrData);

                        $('#shipping-information-summary').find('.a-step-result p').text(addrData);
                        this.setThisShippingAddress();
                        stepNavigator.navigateTo('shipping_method');

                    }
                } else {
                    stepNavigator.next();

                }


            },
            navigateToPrevStep: function () {

                stepNavigator.prev();
            },
            /**
             * @param {Object} address
             */
            onAddressChange: function () {
                //vary bad code starts here
                //select2
                $('#billing_information').find('.mage-error').remove();
                var selected = $('#select_billing option:selected').val();

                var selectedOptionObject = addressList().filter(function (address) {
                    return address.getAddressInline() == selected;
                });
                this.selectedAddress = selectedOptionObject[0];

            },
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },
            setThisShippingAddress: function(){
                checkoutData.setSelectedShippingAddress(this.selectedAddress.getKey());
            },
            getAddressObject: function() {

            }

        });
    }
);