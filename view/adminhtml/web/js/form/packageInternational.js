define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function ($, uiRegistry, select) {
    'use strict';

    uiRegistry.get('index = ek_packstation').hide();
    uiRegistry.get('index = ek_postfiliale').hide();
    uiRegistry.get('index = ek_postnummer').hide();

    return select.extend({

        initialize: function () {
            this._super()

            if (uiRegistry.get('index = product_type').value() != 'EK') {
                this.hide();
            }

            if(this.value() == 'DE') {
                this.showFields();
            }

            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            if(uiRegistry.get('index = ek_country').value() == 'DE') {
                this.showFields();
            }
            else {
                this.hideFields();
            }

            return this._super();
        },
        showFields: function () {
            uiRegistry.get('index = ek_packstation').show();
            uiRegistry.get('index = ek_postfiliale').show();
            uiRegistry.get('index = ek_postnummer').show();
        },
        hideFields: function () {
            uiRegistry.get('index = ek_packstation').hide();
            uiRegistry.get('index = ek_postfiliale').hide();
            uiRegistry.get('index = ek_postnummer').hide();
        }
    });
});
