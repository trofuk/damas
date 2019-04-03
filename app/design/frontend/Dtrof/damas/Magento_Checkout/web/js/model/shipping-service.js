/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'jquery', 'select2'
    ],
    function (ko, checkoutDataResolver, $) {
        "use strict";
        var shippingRates = ko.observableArray([]);
        return {
            isLoading: ko.observable(false),
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function(ratesData) {
                
                //label animation for input
                var $inputGroup = $('.input-group');
                if($inputGroup.length){
                    $inputGroup.each(function(){
                        var $inpt = $('input', $(this));
                        var $nextLabel = $('label', $(this));
                        if($nextLabel.length){
                            if($inpt.val()){
                                $nextLabel.addClass('active');
                            }
                            $inpt.focus(function(){
                                if( !$nextLabel.hasClass('active') ){
                                    $nextLabel.addClass('active');
                                }
                            }).blur(function(){
                                if( !$inpt.val().length ){
                                    $nextLabel.removeClass('active');
                                }
                            });
                        }
                        $inpt.on('click', function(){
                            if($(this).hasClass('mage-error')){
                                $(this).removeClass('mage-error');
                            }
                        });
                    });
                }//label animation for input


                


                //select2
                var $select = $('#shipping select');
                if($select.length) {
                    $select.each(function(){
                        var customClass;
                        if( $(this).hasClass('orange-sel')) {
                            customClass = 'form-filter-drop-orange';
                        } else if( $(this).closest('.drop-size').length ){
                            customClass = 'select2-drop-size';
                        } else if( $(this).closest('.drop-gift').length ){
                            customClass = 'select2-drop-gift';
                        } else if( $(this).closest('.drop-silver').length ){
                            customClass = 'select2-drop-silver';
                        }  else if( $(this).closest('.drop-dark').length ){
                            customClass = 'select2-drop-dark';
                        } else {
                            customClass = 'form-filter-drop';
                        }
                        $(this).select2({
                            minimumResultsForSearch: -1,
                            dropdownCssClass: customClass,
                            containerCssClass: ''
                        });
                        if(!$(this).parent().hasClass('select-wrap')){
                            $(this).parent().addClass('select-wrap drop-silver');
                        }
                    });
                }//select2
                
                shippingRates(ratesData);
                shippingRates.valueHasMutated();
                checkoutDataResolver.resolveShippingRates(ratesData);
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getShippingRates: function() {
                return shippingRates;
            }
        };
    }
);
