/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({

            /**
             * @return {*}
             */
            isDisplayed: function () {

                //quantity
                //console.log('order-review input  '+$('.quantityBlock input').val());
                //console.log('.quantityBlock a.up ' + $('.quantityBlock a.up').html() );
                //console.log('order-reviewDDOWNNNNNNNNNN 1 '+$('.quantityBlock a.down').html());
                var $inpt, qty;
                $('.quantityBlock .up').on('click', function(e){
                    e.preventDefault();
                    $inpt = $(this).parent().find('input');
                    qty = parseInt( $inpt.val() );
                    $inpt.val(qty+1);
                });
                $('.quantityBlock .down').on('click',function(e){
                    e.preventDefault();
                    $inpt = $(this).parent().find('input');
                    qty = parseInt( $inpt.val() );
                    if(qty > 1){$inpt.val(qty-1)}
                });
                //quantity


                return this.isFullMode();
            }
        });
    }
);
