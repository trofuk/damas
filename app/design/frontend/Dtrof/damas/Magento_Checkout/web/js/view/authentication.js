/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer',
        'mage/validation',
        'Magento_Checkout/js/model/authentication-messages',
        'Magento_Checkout/js/model/full-screen-loader',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/view/sidebar-updater'
    ],
    function($, Component, loginAction, customer, validation, messageContainer, fullScreenLoader, _, stepNavigator, sidebarUpdater) {
        'use strict';
        var checkoutConfig = window.checkoutConfig;

        return Component.extend({
            isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
            isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
            registerUrl: checkoutConfig.registerUrl,
            forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
            autocomplete: checkoutConfig.autocomplete,
            defaults: {
                template: 'Magento_Checkout/authentication'
            },
            authenticationFormStoredInVariableNotShowed : window.authenticationFormStoredInVariableNotShowed,
            isVisible: function() {
                //return !quote.isVirtual() && stepNavigator.isProcessed('billing_information');
                var element = $('#authentication');
                return element.hasClass('active');
            },
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'authentication',
                    //step alias
                    null,
                    //step title value
                    'Authentication',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    14
                )

                return this;
            },

            /** Is login form enabled for current customer */
            isActive: function() {
                return !customer.isLoggedIn();
            },
            showIfUserIsNotLogged: function (){
                var isLogged = customer.isLoggedIn();
                if (!isLogged){
                    $('#authentication').addClass('active');
                    return true;
                }
                return !isLogged;
            },
            navigate: function () {

            },
            /** Provide login action */
            login: function(loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });

                if($(loginForm).validation()
                    && $(loginForm).validation('isValid')
                ) {
                    fullScreenLoader.startLoader();
                    loginAction(loginData, checkoutConfig.checkoutUrl, undefined, messageContainer).always(function() {
                        fullScreenLoader.stopLoader();
                    });
                }
            },

            navigateToNextStep: function () {

                stepNavigator.next();
            },
            updateSidebarInfo: function(){
                sidebarUpdater.updateSidebarInfo();
            },
            redirectToRegistrationPage: function () {
                window.location.href = '/customer/account/login/';
            },

            navigateToAddress: function() {
                stepNavigator.navigateTo('shipping');
            }
        });
    }
);
