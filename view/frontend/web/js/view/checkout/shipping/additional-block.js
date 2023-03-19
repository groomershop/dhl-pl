define([
    'uiComponent',
    'mage/url',
    'Magento_Checkout/js/model/quote'
], function (Component, url, quote) {
    'use strict';
    var parcelshopMethodCodes = ['parcelshop_cod', 'parcelshop'];
    var courierMethodCode = ['courier', 'dhl12', 'dhl09', 'evening', 'cod'];
    var show_hide_custom_blockConfig = window.checkoutConfig.show_hide_custom_block;
    var courier;
    jQuery(document).on('click', '.table-checkout-shipping-method tr', function() {
        courier = jQuery(this).find( ".col-method > input.radio").val();
        if(courier == 'dhl_dhl24pl_courier' || courier == 'dhl_dhl24pl_dhl12' || courier == 'dhl_dhl24pl_evening' || courier == 'dhl_dhl24pl_cod' || courier == 'dhl_dhl24pl_dhl09') {
            jQuery('#dhl_dhl24pl_neighbours_block').show();
            jQuery('#dhl_dhl24pl_parcelshop_block').hide();
        } else if (courier == 'dhl_dhl24pl_parcelshop' || courier == 'dhl_dhl24pl_parcelshop_cod') {
            jQuery('#dhl_dhl24pl_courier_block').hide();
            jQuery('#dhl_dhl24pl_neighbours_block').hide();
            jQuery('#dhl_dhl24pl_parcelshop_block').show();
            jQuery('#neighbours_block').prop('checked', false);
        } else {
            jQuery('#dhl_dhl24pl_courier_block').hide();
            jQuery('#dhl_dhl24pl_neighbours_block').hide();
            jQuery('#dhl_dhl24pl_parcelshop_block').hide();
            jQuery('#neighbours_block').prop('checked', false);
        }
    });

    jQuery(document).on('click', '#neighbours_block', function() {
        if(jQuery(this).is(':checked')) {
            jQuery('#dhl_dhl24pl_courier_block').show();
            jQuery('#neighbours_block').val(true);
        }
        else {
            jQuery('#dhl_dhl24pl_courier_block').hide();
            jQuery('#neighbours_block').val(false);
        }
    });

    jQuery(document).on('click', '#dhllink', function() {
        var countryId = '';
        var type = '';
        var urlNew = '';
        if(jQuery("select[name='country_id']").val() != 'PL') {
            countryId = '/country/' + jQuery("select[name='country_id']").val();
            type = countryId == 'DE' ? '/type/packStation' : '/type/parcelShop';
        }

        if(courier == 'dhl_dhl24pl_parcelshop_cod') {
            type = '/type/lmcod';
        }
        urlNew = 'dhl/shipment/lmmap'+ countryId + type;

        var childWindow = open( url.build(urlNew), "Mapa Parcelshop", "status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width=860,height=890" );
        if( childWindow.opener == null ) childWindow.opener = self;
    });

    return Component.extend({
        defaults: {
            template: 'DHL_Dhl24pl/checkout/shipping/additional-block'
        },
        canVisibleBlock: show_hide_custom_blockConfig,

        getVisibleCourier: function() {
            if(jQuery('#s_method_dhl_dhl24pl_courier').is(':checked')) {
                var checked = jQuery('#s_method_dhl_dhl24pl_courier').is(':checked');
            }
            if(jQuery('#s_method_dhl_dhl24pl_dhl12').is(':checked')) {
                var checked = jQuery('#s_method_dhl_dhl24pl_dhl12').is(':checked');
            }
            if(jQuery('#s_method_dhl_dhl24pl_dhl09').is(':checked')) {
                var checked = jQuery('#s_method_dhl_dhl24pl_dhl09').is(':checked');
            }
            if(jQuery('#s_method_dhl_dhl24pl_evening').is(':checked')) {
                var checked = jQuery('#s_method_dhl_dhl24pl_evening').is(':checked');
            }
            if(jQuery('#s_method_dhl_dhl24pl_cod').is(':checked')) {
                var checked = jQuery('#s_method_dhl_dhl24pl_cod').is(':checked');
            }
            if (checked) {
                return true;
            } else {
                return false;
            }
        },

        getLmmapUrl: function() {
            return url.build('dhl/shipment/lmmap/');
        },

        isParcelshop: function() {
            return this.isMethodCodeValid(parcelshopMethodCodes);
        },

        isCourier: function() {
            return this.isMethodCodeValid(courierMethodCode);
        },

        isMethodCodeValid: function(methodCodes) {
            return quote && quote.shippingMethod() && quote.shippingMethod().carrier_code === 'dhl_dhl24pl' && methodCodes.includes(quote.shippingMethod().method_code);

        }
    });
});
