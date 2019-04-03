define(
    [
        'ko',
        'uiComponent',
        'underscore'
    ],
    function (
        ko,
        Component,
        _
    ) {
        'use strict';
        /**
         *
         * mystep - is the name of the component's .html template,
         * your_module_dir - is the name of the your module directory.
         *
         */
        return Component.extend({

            navigateToPrevStep: function () {

                stepNavigator.prev();
            }
        });
    }
);