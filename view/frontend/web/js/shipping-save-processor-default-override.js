console.log('init DHL/Dhl24pl/view/frontend/web/js/shipping-save-processor-default-override.js')
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function (
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
						//DHL_Dhl24pl START
                        extension_attributes: {
                            is_neighbours: jQuery('[name="is_neighbours"]').val(),
                            courier_neighbor_name: jQuery('[name="courier_neighbor_name"]').val(),
                            courier_neighbor_postcode: jQuery('[name="courier_neighbor_postcode"]').val(),
                            courier_neighbor_city: jQuery('[name="courier_neighbor_city"]').val(),
                            courier_neighbor_street: jQuery('[name="courier_neighbor_street"]').val(),
                            courier_neighbor_houseNumber: jQuery('[name="courier_neighbor_houseNumber"]').val(),
                            courier_neighbor_apartmentNumber: jQuery('[name="courier_neighbor_apartmentNumber"]').val(),
                            courier_neighbor_phoneNumber: jQuery('[name="courier_neighbor_phoneNumber"]').val(),
                            courier_neighbor_emailAddress: jQuery('[name="courier_neighbor_emailAddress"]').val(),
                            parcelshop_sap: jQuery('[name="parcelshop_sap"]').val(),
                            parcelshop_zip: jQuery('[name="parcelshop_zip"]').val(),
                            parcelshop_city: jQuery('[name="parcelshop_city"]').val()
                        }
                        //DHL_Dhl24pl END
                    }
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);
