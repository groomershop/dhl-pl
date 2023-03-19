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
            var linkUrl = uiRegistry.get('index = packageSchemaUrl');

            $.ajax({
                url: linkUrl.value(),
                showLoader: false,
                data: {form_key: window.FORM_KEY, 'name':value},
                type: "POST",
                dataType : 'json',
                success: function(result) {
                    uiRegistry.get('index = payerType').set('value', result.shipment.payer);
                    uiRegistry.get('index = package_type').value(result.shipment.package_type);
                    uiRegistry.get('index = weight').value(result.shipment.weight);
                    uiRegistry.get('index = width').value(result.shipment.width);
                    uiRegistry.get('index = height').value(result.shipment.height);
                    uiRegistry.get('index = length').value(result.shipment.lenght);
                    uiRegistry.get('index = quantity').value(result.shipment.quantity);
                    if(uiRegistry.get('index = recipient_country').value() == 'PL') {
                        uiRegistry.get('index = product_type').set('value', result.shipment.product_type);
                    }
                    uiRegistry.get('index = costsCenter').value(result.shipment.costs_center);
                    uiRegistry.get('index = content').value(result.shipment.content);
                    uiRegistry.get('index = deliveryToNeighbour').value((result.shipment.delivery_to_neighbour == '1') ? true : false);
                    uiRegistry.get('index = deliveryOnSaturday').value((result.shipment.delivery_on_saturday == '1') ? true : false);
                    uiRegistry.get('index = insurance').value((result.shipment.insurance == '1') ? true : false);
                    if(result.shipment.insurance == '1' && uiRegistry.get('index = insuranceValue').value() == '') {
                        uiRegistry.get('index = insuranceValue').set('value', result.shipment.insurance_price);
                    }
                    uiRegistry.get('index = deliveryEvening').value((result.shipment.delivery_evening == '1') ? true : false);
                    uiRegistry.get('index = deliveryToLM').value((result.shipment.delivery_to_lm == '1') ? true : false);
                    uiRegistry.get('index = selfCollect').value((result.shipment.self_collect == '1') ? true : false);
                    uiRegistry.get('index = proofOfDelivery').value((result.shipment.proof_of_delivery == '1') ? true : false);
                    uiRegistry.get('index = returnOnDelivery').value((result.shipment.return_on_delivery == '1') ? true : false);
                    uiRegistry.get('index = nonStandard').value((result.shipment.non_standard == '1') ? true : false);
                    uiRegistry.get('index = pickupOnSaturday').value((result.shipment.pickup_on_saturday == '1') ? true : false);
                    uiRegistry.get('index = predeliveryInformation').value((result.shipment.predelivery_information == '1') ? true : false);
                    uiRegistry.get('index = name').set('value', result.shipment.sender);
                    uiRegistry.get('index = comment').set('value', result.shipment.comment);

                    uiRegistry.get('index = collectOnDelivery').value(result.shipment.cod);
                    uiRegistry.get('index = collectOnDeliveryValue').value(result.shipment.cod_price);

                    if(result.shipment.packageType == 'ENVELOPE') {
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

                }
            });

            return this._super();
        }
    });
});
