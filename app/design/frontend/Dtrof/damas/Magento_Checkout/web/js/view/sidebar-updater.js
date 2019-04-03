/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery'
    ],
    function($) {
        'use strict';
        return {
            test: function(){
                console.log('teste!!!');
            },
            //i use jquery instead ko bindings because of lack of time. sorry - am
            updateSidebarInfo: function(id, data){
                console.log();
            }
        };
    }
);
