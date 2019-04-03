define([
    "jquery",
    "jquery/ui"
], function ($) {


    $( document ).ready(function() {

        var oyiGifts = new Class.create();

        oyiGifts.prototype = {

            initialize: function(){
                console.log('oyiGifts - initialize');
                $('#edit_form').prepend('<div class="oyi-message" style="padding-bottom: 10px; line-height: 19px;"></div>');
            },
            showNextAttribute: function(store_id, configuration_id, attribute_id, option_id, value_id, current, parent) {
                console.log('oyiGifts - showNextAttribute');
                //console.log(store_id, configuration_id, attribute_id, option_id, value_id);
                console.log('parent', parent);
                var parent_id = parseInt($('#'+parent).attr('data-parent-id'));
                if (typeof parent_id == 'undefined') {
                    parent_id = 0;
                }
                if(isNaN(parent_id)) {
                    parent_id = 0;
                }
                $.ajax({
                    url: location.protocol + '//' + location.host+'/admin/oyigifts/configuration/link/',
                    dataType: 'json',
                    cache: false,
                    data: {
                        store_id: store_id,
                        configuration_id: configuration_id,
                        attribute_id: attribute_id,
                        option_id: option_id,
                        value_id: value_id,
                        parent_id: parent_id,
                        form_key: window.FORM_KEY
                    },
                    beforeSend : function(){
                        $('.oyi-message').text('Loading. Please wait.');
                    },
                    success: function(data) {
                        $('#'+current).attr('data-parent-id', data.link_id);
                        $('#'+current+'_'+value_id).addClass('primary');
                        $('#'+current+'_'+value_id).addClass('link_'+ data.link_id +'_'+ parent_id);
                        $('.remove_'+data.link_id+'_'+parent_id).remove();
                        $('#'+current+'_'+value_id).after('<button type="button"' +
                            'class="remove_'+ data.link_id +'_'+ parent_id +' scalable primary"' +
                            'style="margin-left: -5px; padding-left: 5px; padding-right: 5px;"' +
                            'onclick="window.oyiGiftsObj.removeAttribute('+ data.link_id +','+ parent_id +',\''+current+'\');">' +
                            '<span><span><span>X</span></span></span></button>');
                        if(data.last == 1) {
                            $('#content_'+parent).html(data.content);
                        } else {
                            $('#content_'+current).html(data.content);
                        }
                    }
                }).done(function() {
                    $('.oyi-message').text('');
                });
            },
            removeAttribute: function(link_id, parent_link_id, parent) {
                $.ajax({
                    url: location.protocol + '//' + location.host+'/admin/oyigifts/configuration/remove/',
                    dataType: 'json',
                    cache: false,
                    data: {
                        link_id: link_id,
                        parent_link_id: parent_link_id,
                        form_key: window.FORM_KEY
                    },
                    beforeSend : function(){
                        $('.oyi-message').text('Loading. Please wait.');
                    },
                    success: function(data) {
                        if(data.status == 1) {
                            $('.link_'+link_id+'_'+parent_link_id).removeClass('primary');
                            $('.remove_'+link_id+'_'+parent_link_id).remove();
                            $('#content_'+parent).html('');
                        }
                    }
                }).done(function() {
                    $('.oyi-message').text('');
                });
            },
            optionImagePreview: function($this,option_id)
            {
                var fr = new FileReader();
                var preview_block = $('label[for=option_'+option_id+']').find('.option-images-preview');
                fr.onloadend = function () {
                    preview_block.html('<img src="'+fr.result+'" style="max-height: 60px;">');
                };
                if ($this) {
                    fr.readAsDataURL($this.files[0]);
                } else {
                    preview_block.html('');
                }
            }
        };

        window.oyiGiftsObj = new oyiGifts();
    });
});
