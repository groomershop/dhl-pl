define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function ($, uiRegistry, select) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            if(uiRegistry.get('index = package_type').value() === 'ENVELOPE') {
                uiRegistry.get('index = weight').hide();
                uiRegistry.get('index = width').hide();
                uiRegistry.get('index = height').hide();
                uiRegistry.get('index = length').hide();
            }
            else {
                uiRegistry.get('index = weight').show();
                uiRegistry.get('index = width').show();
                uiRegistry.get('index = height').show();
                uiRegistry.get('index = length').show();
            }

            return this._super();
        },
    });
});
