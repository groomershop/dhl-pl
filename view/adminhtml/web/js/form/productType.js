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
            if (uiRegistry.get('index = product_type').value() == 'EK') {
                uiRegistry.get('index = ek_country').show();
            } else {
                uiRegistry.get('index = ek_country').hide();
                uiRegistry.get('index = ek_packstation').hide();
                uiRegistry.get('index = ek_postfiliale').hide();
                uiRegistry.get('index = ek_postnummer').hide();
            }

            return this._super();
        },
    });
});
