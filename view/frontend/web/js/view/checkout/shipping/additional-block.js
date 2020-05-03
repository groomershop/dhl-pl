define([
    'uiComponent',
    'mage/url'

], function (Component, url) {
    'use strict';
    var show_hide_custom_blockConfig = window.checkoutConfig.show_hide_custom_block;
    jQuery(document).on('click', '.table-checkout-shipping-method tr', function() {
        var wybor = jQuery(this).find( ".col-method > input.radio").val();
        if(wybor == 'dhl_dhl24pl_courier') {
            jQuery('#dhl_dhl24pl_courier_block').show();
            jQuery('#dhl_dhl24pl_parcelshop_block').hide();
        } else if (wybor == 'dhl_dhl24pl_parcelshop') {
            jQuery('#dhl_dhl24pl_courier_block').hide();
            jQuery('#dhl_dhl24pl_parcelshop_block').show();
        } else {
            jQuery('#dhl_dhl24pl_courier_block').hide();
            jQuery('#dhl_dhl24pl_parcelshop_block').hide();
        }
    });
    jQuery(document).on('click', '#dhllink', function() {
        var link = jQuery("#lmMapUrl").val();
        var url = document.getElementById('lmMapUrl').value;
        var childWindow = open( url, "Mapa Parcelshop", "status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width=860,height=890" );
        if( childWindow.opener == null ) childWindow.opener = self;
    });

    return Component.extend({
        defaults: {
            template: 'DHL_Dhl24pl/checkout/shipping/additional-block'
        },
        getVisibleCourier: function() {
            var checked = jQuery('#s_method_dhl_dhl24pl_courier').is(':checked');
            if (checked) {
                return true;
            } else {
                return false;
            }
        },
        getVisibleParcelshop: function() {
            var checked = jQuery('#s_method_dhl_dhl24pl_parcelshop').is(':checked');
            if (checked) {
                return true;
            } else {
                return false;
            }
        },
        getLmmapUrl: function() {
            return url.build('dhl/shipment/lmmap');
        },
        canVisibleBlock: show_hide_custom_blockConfig
    });
});
