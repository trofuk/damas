define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';
    $( document ).ready(function() {
        var oyiGifts = new Class.create();
        oyiGifts.prototype = {
            initialize: function () {
                //console.log('oyiGifts - initialize');
                //$(document).on('click','.showAdvisorStep, .filterPopupGifts, .return',function(e){
                //    if($(this).hasClass('close-popup')){
                //        $.fancybox.close();
                //    }else{
                //        var  url = $(this).attr('href');
                //        openPopup(url);
                //    }
                //    return false;
                //});

                $('.filterSections .filterCircle').each(function(){
                    if( !$(this).hasClass('done') ){
                        $(this).addClass('done');
                        var label = $('label', this);
                        $('input', this).change(function(){
                            label.removeClass('checked');
                            $(this).parent().addClass('checked');
                        });
                    }
                });

                //$('.giftsPopup , .giftsPopup > a').on('click',function(e){
                //    var  id = $(this).data('id'),
                //        add_id = '';
                //    if(typeof id != 'undefined' && id > 0){
                //        add_id = '?id='+id;
                //    }
                //    var url = '/gifts/advisor/popup'+add_id;
                //    openPopup(url);
                //    return false;
                //});
            },
            step: function(configuration_id,attribute_id,parent_id){
                //$('.link_'+configuration_id+'_'+attribute_id+'_'+parent_id).find('input[type=radio]').attr( "checked" );
                $.ajax({
                    url: location.protocol + '//' + location.host+'/gifts/advisor/step/',
                    dataType: 'json',
                    cache: false,
                    data: {
                        configuration_id: configuration_id,
                        attribute_id: attribute_id,
                        parent_id: parent_id,
                        form_key: window.FORM_KEY
                    },
                    beforeSend : function(){
                        //console.log('oyiGifts - step - beforeSend');
                    },
                    success: function(data) {
                        //console.log('oyiGifts - step - success');
                        //if(data.last == 0) {
                            $('#content_' + data.container).html(data.content);
                        //}

                    }
                }).done(function(data) {
                    //console.log('oyiGifts - step - done');
                    $('.filterSections .filterCircle').each(function(){
                        if( !$(this).hasClass('done') ){
                            $(this).addClass('done');
                            var label = $('label', this);
                            $('input', this).change(function(){
                                label.removeClass('checked');
                                $(this).parent().addClass('checked');
                            });
                        }
                    });
                });
            },
            showSubmitButton: function() {
                //console.log('oyiGifts - showSubmitButton');
                $('.filterButtonsBlock').show();
            }
        };
         function openPopup(url,id){
            $.ajax({
                type: "post",
                url: url,
                success: function(data){
                    $.fancybox({
                        content : data,
                        type: 'ajax',
                        afterShow: function(){

                        }
                    });
                }
            });
        }
        window.oyiGifts = new oyiGifts();
    });
});