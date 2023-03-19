define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../model/shipping-rates-validator/dhl_dhl24pl',
    '../model/shipping-rates-validation-rules/dhl_dhl24pl'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    dhl24plShippingRatesValidator,
    dhl24plShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('dhl_dhl24pl', dhl24plShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('dhl_dhl24pl', dhl24plShippingRatesValidationRules);

    return Component;
});
