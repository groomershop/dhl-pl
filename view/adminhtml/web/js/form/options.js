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
            var linkUrl = uiRegistry.get('index = url');

            $.ajax({
                url: linkUrl.value(),
                showLoader: false,
                data: {form_key: window.FORM_KEY, 'name':value},
                type: "POST",
                dataType : 'json',
                success: function(result) {
                    if(value != '-- wybierz --') {
                        uiRegistry.get('index = personName').value(result.name);
                        uiRegistry.get('index = city').value(result.city);
                        uiRegistry.get('index = emailAddress').value(result.email);
                        uiRegistry.get('index = houseNumber').value(result.street_number);
                        uiRegistry.get('index = phoneNumber').value(result.phone);
                        uiRegistry.get('index = postcode').value(result.postcode);
                        uiRegistry.get('index = street').value(result.street);
                        uiRegistry.get('index = apartmentNumber').value(result.house_number);
                        uiRegistry.get('index = sap').value(result.sap);
                    }
                }
            });

            return this._super();
        },
    });
});
