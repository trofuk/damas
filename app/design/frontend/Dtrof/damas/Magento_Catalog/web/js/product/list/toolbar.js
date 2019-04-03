/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function($) {
    /**
     * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('mage.productListToolbarForm', {

        options: {
            modeControl: '[data-role="mode-switcher"]',
            //directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            onlineControl:'.online-offline-checkbox a',
            onlineCheckboxControl:'.online-offline-checkbox input[type=checkbox]',
            mode: 'product_list_mode',
            //direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            online:'online_product',
            modeDefault: 'grid',
            //directionDefault: 'asc',
            orderDefault: '',
            limitDefault: '9',
            onlineDefault: '',
            url: ''
        },

        _create: function () {
            this._bind($(this.options.modeControl), this.options.mode, this.options.modeDefault);
            //this._bind($(this.options.directionControl), this.options.direction, this.options.directionDefault);
            this._bind($(this.options.orderControl), this.options.order, this.options.orderDefault);
            this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
            this._bind($(this.options.onlineControl), this.options.online, this.options.onlineDefault);
            this._bind($(this.options.onlineCheckboxControl), this.options.online, this.options.onlineDefault);

            if (typeof window.history.replaceState === "function") {
                window.history.replaceState({url: document.URL}, document.title);

                //setTimeout(function() {
                //    window.onpopstate = function(e){
                //        if(e.state){
                //            if (typeof window.history.pushState === 'function') {
                //                window.history.pushState({url: e.state.url}, '', e.state.url);
                //            }
                //        }
                //    };
                //}, 0)
            }
        },

        _bind: function (element, paramName, defaultValue) {
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
        },

        _processLink: function (event) {
            event.preventDefault();
            if(event.data.paramName == 'online_product'){
                if($(event.currentTarget).parents('label.checkbox').hasClass('active')){
                    $(event.currentTarget).parents('label.checkbox').removeClass('active');
                }else{
                    $('.online-offline-checkbox').find('label.checkbox').removeClass('active');
                    $(event.currentTarget).parents('label.checkbox').addClass('active');
                }

            }
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },

        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },
        
        itemDetailsShow: function(parent, button, w1, w2) {
            var item = $(parent);

            if(item.length) {
                item.each(function(){
                    var control = $(this).find(button),
                        content = $(this).find(w1);
                    var window2 = $(this).find(w2);

                    control.on('click', function(){
                        if(window2.hasClass('opened')){
                            window2.removeClass('opened');
                        }
                        content.toggleClass('opened');
                        return false;
                    })
                })
            }
        },
        
        changeUrl: function (paramName, paramValue, defaultValue) {
            var url = [],
                limit = '',
                order = '',
                mode = '',
                online = '',
                limitVal = $(this.options.limitControl).val(),
                orderVal = $(this.options.orderControl).val(),
                onlineVal = $('.online-offline-checkbox .active').find('a').data('value');

            if(limitVal != this.options.limitDefault && limitVal != ''){
                limit = this.options.limit+'='+limitVal;
                url += limit+'&';
            }
            if(orderVal != this.options.orderDefault && orderVal != ''){
                order = this.options.order+'='+orderVal;
                url += order+'&';
            }

            if(paramName == 'product_list_mode'){
                if(paramValue == 'grid'){
                    $('.active-grid').removeClass('hidden').show();
                    $('.active-list').addClass('hidden').hide();
                }
                if(paramValue == 'list'){
                    $('.active-list').removeClass('hidden').show();
                    $('.active-grid').addClass('hidden').hide();
                }

                if(paramValue != this.options.modeDefault && paramValue != ''){
                    mode = this.options.mode+'='+paramValue;
                    url += mode+'&';
                }

            }else{
                var modeVal = $('.modes-mode.active:visible').data('value');
                if(modeVal != this.options.modeDefault && modeVal != ''){
                    mode = this.options.mode+'='+modeVal;
                    url += mode+'&';
                }

            }
            if(typeof onlineVal != 'undefined'){
                if(onlineVal != ''){
                    online = this.options.online+'='+onlineVal;
                    url += online+'&';
                }
            }

            var urlPaths = this.options.url.split('?'),
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[parameters[0]] = parameters[1] !== undefined
                    ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData = $.param(paramData);
            if(paramData != ''){
                paramData += '&';
            }

            var cat = $('.caption-wrap h1').data('cat');
            var self = this;

            $.ajax({
                url:'/ajax/onlineoffline/index?'+paramData+'cat='+cat+'&'+url+'',
                type: 'post',
                success: function(data){
                    requirejs(
                        ['jquery',
                            'Magento_Customer/js/customer-data',
                            'select2',
                            "validation",
                            'mage/mage',
                            'ko',
                            "jquery/ui",
                            'jqueryForm',
                            'maincustom',
                            'MagicToolbox_Magic360/js/magic360',
                            'Magento_Swatches/js/SwatchRenderer',
                            'MagicToolbox_Magic360/js/SwatchRenderer']
                        ,function($,customerData) {
                            window.onpopstate = function(e){
                                if(e.state){
                                    if (typeof window.history.pushState === 'function') {
                                        window.history.pushState({url: e.state.url}, '', e.state.url);
                                    }
                                }
                            };

                            if($('.add-wishlist-is-login').length > 0){
                                $('[data-action="add-to-wishlist"]').on('click',function(e){
                                    var $this =  $(this),
                                        post = $this.data('post');

                                    $.ajax({
                                        url : '/wishlistajax/index/add',
                                        type: 'post',
                                        data: post.data,
                                        success: function(data)
                                        {
                                            if(data.success == true){
                                                customerData.reload(['wishlist'],true);
                                            }
                                        },
                                        dataType: 'json'
                                    });
                                    return false;
                                });
                            }
                        });
                    var htmlObject = $('.filtered-results').find('.items-wrap').html(data);
                    htmlObject.trigger('contentUpdated');
                    self.itemDetailsShow('.filtered-results .items-wrap .item', '.show-item-details', '.item-details-popup', '.item-details-share' );
                    self.itemDetailsShow('.additionalProductList .product-item', '.show-item-details', '.item-details-popup', '.item-details-share');
                    self.itemDetailsShow('.products .itemList', '.show-item-details', '.item-details-popup', '.item-details-share');
                    self.itemDetailsShow('.products>.item', '.share-this', '.item-details-share', '.item-details-popup');
                }
            });
        }
    });

    return $.mage.productListToolbarForm;
});
