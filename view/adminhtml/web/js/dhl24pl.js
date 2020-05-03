/*
 * @Author: zerokool - Nguyen Huu Tien
 * @Date:   2015-07-16 13:17:04
 * @Last Modified by:   zero
 * @Last Modified time: 2015-09-26 20:37:57
 */
var licznikPaczek = 0;
'use strict';
require(['jquery'], function($) {
    $(document).ready(function() {
        init();
    });

    function init() {
        if ( !licznikPaczek || licznikPaczek == undefined )  {
            licznikPaczek = document.querySelectorAll('.wiersz_paczki').length;
        }
        manageCountry();
        showHideSas();
        showHideInsurance();
        showHideCod();
        showHideLm();
        if($("#payerT").is(':checked')) {
            enableSender(false);
        } else {
            disableSender();
        }
        if ($("#productEK").length > 0) {
            if($("#productEK").is(':checked')) {
                enableSender(false);
                $('#productEkDiv').show();
            } else {
                $('#productEkDiv').hide();
                $('#ek_packstation').val('');
                $('#ek_postfiliale').val('');
                $('#ek_postnummer').val('');
            }
        }
        $( "#shipment" ).change(function() {
            changeShipment();
        });
        $( "#name" ).change(function() {
            changeSender();
        });
        $( ".product" ).click(function() {
            changeProduct(this);
        });
        $( ".payer" ).click(function() {
            changePayer(this);
        });
        $( "#hoursLink" ).click(function() {
            getHours();
        });
        $( "#search_link_paczkomat" ).click(function() {
            show2EuropeMap('packStation');
        });
        $( "#search_link_parcelshop" ).click(function() {
            show2EuropeMap('parcelShop');
        });
        $( "#ek_country" ).change(function() {
            changeCountry();
        });
        //
        $( "#lmMapId" ).click(function() {
            showLmMap();
        });
        $( "#deliveryToNeighbour" ).click(function() {
            showHideSas();
        });
        $( "#deliveryToLM" ).click(function() {
            showHideLm();
        });
        $( "#insurance" ).click(function() {
            showHideInsurance();
        });
        $( "#collectOnDelivery" ).click(function() {
            showHideCod();
        });
        $( document ).on( "change", ".packageType", function() {
            przelaczanie_typu_paczki(this);
        });
        $( "#przycisk-dodawania-paczki" ).click(function() {
            nowy_wiersz();
        });
        $( document ).on( "click", ".remove", function() {
            usun_wiersz(this);
        });
        $( "#saveButton" ).click(function() {
            $('#shipmentForm').submit();
        });
    }

    /*
     * Funkcja dodaje klase do obiektu
     */
    function addClass(id, className) {
        $('#' + id).addClass(className);
    }

    /**
     * Funkcja usuwa klasę z obiektu
     */
    function removeClass(id, className) {
        $('#' + id).removeClass(className);
    }

    function disableSender() {
        addClass('postcode', 'disabled');
        addClass('city', 'disabled');
        addClass('street', 'disabled');
        addClass('houseNumber', 'disabled');
        addClass('apartmentNumber', 'disabled');
        addClass('personName', 'disabled');
        addClass('phoneNumber', 'disabled');
        addClass('emailAddress', 'disabled');
        addClass('sap', 'disabled');

        $("#postcode").attr('disabled','disabled');
        $("#city").attr('disabled','disabled');
        $("#street").attr('disabled','disabled');
        $("#houseNumber").attr('disabled','disabled');
        $("#apartmentNumber").attr('disabled','disabled');
        $("#personName").attr('disabled','disabled');
        $("#phoneNumber").attr('disabled','disabled');
        $("#emailAddress").attr('disabled','disabled');
        $("#sap").attr('disabled','disabled');
        $('#div_nadawca2').hide();
        $('#div_nadawca').show();
    }

    function enableSender(withClear) {
        if (withClear) {
            $("#postcode").val('');
            $("#city").val('');
            $("#street").val('');
            $("#houseNumber").val('');
            $("#apartmentNumber").val('');
            $("#personName").val('');
            $("#phoneNumber").val('');
            $("#emailAddress").val('');
            $("#sap").val('');
        }
        $("#postcode").removeAttr('disabled');
        $("#city").removeAttr('disabled');
        $("#street").removeAttr('disabled');
        $("#houseNumber").removeAttr('disabled');
        $("#apartmentNumber").removeAttr('disabled');
        $("#personName").removeAttr('disabled');
        $("#phoneNumber").removeAttr('disabled');
        $("#emailAddress").removeAttr('disabled');
        $("#sap").removeAttr('disabled');

        removeClass('postcode', 'disabled');
        removeClass('city', 'disabled');
        removeClass('street', 'disabled');
        removeClass('houseNumber', 'disabled');
        removeClass('apartmentNumber', 'disabled');
        removeClass('personName', 'disabled');
        removeClass('phoneNumber', 'disabled');
        removeClass('emailAddress', 'disabled');
        removeClass('sap', 'disabled');

        $('#div_nadawca').hide();
        $('#div_nadawca2').show();
    }

    function getHours() {
        $.ajax({
            url: $('#hoursUrl').val(),
            data: {form_key: window.FORM_KEY, date: $('#shipmentDate').val(), code: $('#postcode').val()},
            type: 'POST',
            dataType: "json",
            success: function( data ) {
                $('#hoursInfo').html(data);
            }
        });
    }

    function changeShipment(){

        $.ajax({
            url: $('#shipmentUrl').val(),
            data: {form_key: window.FORM_KEY, name: $('#shipment').val()},
            type: 'POST',
            dataType: "json",
            success: function( data ) {
                $('#postcode').val(data.sender.postcode);
                $('#city').val(data.sender.city);
                $('#street').val(data.sender.street);
                $('#houseNumber').val(data.sender.houseNumber);
                $('#apartmentNumber').val(data.sender.apartmentNumber);
                $('#personName').val(data.sender.personName);
                $('#emailAddress').val(data.sender.emailAddress);
                $('#sap').val(data.sender.sap);
                $('#name').val(data.sender.name);
                $('#costsCenter').val(data.shipment.costsCenter);
                $('#content').val(data.shipment.content);
                $('#payer' + data.shipment.payer).prop("checked", true);
                $('#product' + data.shipment.product).prop("checked", true);
                if (data.shipment.courier) {
                    $('#courierHours').show();
                    $('#courierInfo').show();
                } else {
                    $('#courierHours').hide();
                    $('#courierInfo').hide();
                }
                nowy_wiersz_z_danymi(data.shipment.packageType, data.shipment.width, data.shipment.height, data.shipment.length, data.shipment.weight, data.shipment.quantity, data.shipment.nonStandard)

                changePayer($('#payer' + data.shipment.payer));
                changeProduct($('#product' + data.shipment.product));
                $('#isPrice').val();
                if (!isPrice) {
                    if (data.shipment.insurance) {
                        $('#insurance').prop("checked", true);
                        $('#insuranceValue').val(data.shipment.insuranceValue);
                    } else {
                        $('#insurance').attr('checked', false);
                        $('#insuranceValue').val('');
                    }
                    showHideInsurance();
                    if (data.shipment.collectOnDelivery) {
                        $('#collectOnDelivery').prop("checked", true);
                        $('#collectOnDeliveryValue').val(data.shipment.insuranceValue);
                    } else {
                        $('#collectOnDelivery').attr('checked', false);
                        $('#collectOnDeliveryValue').val('');
                    }
                    showHideCod();
                }
                if (data.shipment.predeliveryInformation) {
                    $('#predeliveryInformation').prop("checked", true);
                } else {
                    $('#predeliveryInformation').attr('checked', false);
                }
                if (data.shipment.returnOnDelivery) {
                    $('#returnOnDelivery').prop("checked", true);
                } else {
                    $('#returnOnDelivery').attr('checked', false);
                }
                if (data.shipment.proofOfDelivery) {
                    $('#proofOfDelivery').prop("checked", true);
                } else {
                    $('#proofOfDelivery').attr('checked', false);
                }
                if ($("#deliveryToNeighbour").length > 0) {
                    if (data.shipment.deliveryToNeighbour) {
                        $('#deliveryToNeighbour').prop("checked", true);
                    } else {
                        $('#deliveryToNeighbour').attr('checked', false);
                    }
                    showHideSas();
                }
                if (data.shipment.deliveryEvening) {
                    $('#deliveryEvening').prop("checked", true);
                } else {
                    $('#deliveryEvening').attr('checked', false);
                }
                if (data.shipment.deliveryOnSaturday) {
                    $('#deliveryOnSaturday').prop("checked", true);
                } else {
                    $('#deliveryOnSaturday').attr('checked', false);
                }
                if (data.shipment.pickupOnSaturday) {
                    $('#pickupOnSaturday').prop("checked", true);
                } else {
                    $('#pickupOnSaturday').attr('checked', false);

                }
                if (data.shipment.selfCollect) {
                    $('#selfCollect').prop("checked", true);
                } else {
                    $('#selfCollect').attr('checked', false);
                }
                if ($("#deliveryToLM").length > 0) {
                    if (data.shipment.deliveryToLM) {
                        $('#deliveryToLM').prop("checked", true);
                    } else {
                        $('#deliveryToLM').attr('checked', false);
                    }
                    showHideLm();
                }
            }
        });
    }

    function changeSender(){
        $.ajax({
            url: $('#senderUrl').val(),
            data: {form_key: window.FORM_KEY, name: $('#name').val()},
            type: 'POST',
            dataType: "json",
            success: function( data ) {
                $('#postcode').val(data.postcode);
                $('#city').val(data.city);
                $('#street').val(data.street);
                $('#houseNumber').val(data.houseNumber);
                $('#apartmentNumber').val(data.apartmentNumber);
                $('#personName').val(data.personName);
                $('#phoneNumber').val(data.phoneNumber);
                $('#emailAddress').val(data.emailAddress);
                $('#sap').val(data.sap);
            }
        });
    }

    function changePayer(radio){
        if ($(radio).val() == 'T') {
            enableSender(true);
        } else {
            $('#div_nadawca2').hide();
            $('#div_nadawca').show();
            $.ajax({
                url: $('#senderUrl').val(),
                data: {form_key: window.FORM_KEY, name: $('#name').val()},
                type: 'POST',
                dataType: "json",
                success: function( data ) {
                    $('#postcode').val(data.postcode);
                    $('#city').val(data.city);
                    $('#street').val(data.street);
                    $('#houseNumber').val(data.houseNumber);
                    $('#apartmentNumber').val(data.apartmentNumber);
                    $('#personName').val(data.personName);
                    $('#phoneNumber').val(data.phoneNumber);
                    $('#emailAddress').val(data.emailAddress);
                    $('#sap').val(data.sap);
                    disableSender();
                }
            });
        }
    }

    function changeProduct(radio){
        if ($(radio).val() == 'EK') {
            $('#currencyCurrency').html('EUR');
            if ($("#productEkDiv").length > 0) {
                $("#productEkDiv").show();
            }
        } else {
            $('#currencyCurrency').html('PLN');
            document.getElementById("currencyCurrency").innerHTML = 'PLN';
            if ($("#productEkDiv").length > 0) {
                $("#productEkDiv").hide();
                $("#ek_packstation").val('');
                $("#ek_postfiliale").val('');
                $("#ek_postnummer").val('');
            }
            $.ajax({
                url: $('#recipientUrl').val(),
                data: {form_key: window.FORM_KEY, oid: $('#order_id').val()},
                type: 'POST',
                dataType: "json",
                success: function( data ) {
                    $('#rec_postcode').val(data.rec_postcode);
                    $('#rec_city').val(data.rec_city);
                }
            });
        }
    }

    function show2EuropeMap(type) {
        var url = $('#europeMapUrl').val() + '?country=' + $('#ek_country').val() + '&type=' + type;
        $('#mapType').val(type);
        var childWindow = open( url, "Mapa paczkomatów", "status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width=840,height=820" );
        if( childWindow.opener == null ) childWindow.opener = self;
    }

    function showLmMap() {
        var url = $('#lmMapUrl').val();
        var childWindow = open( url, "Mapa Parcelshop", "status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width=840,height=820" );
        if( childWindow.opener == null ) childWindow.opener = self;
    }

    function showHideInsurance() {
        if($("#insurance").is(':checked')) {
            document.getElementById('insuranceValueDiv').style.display = "";
        } else {
            $('#insuranceValueDiv').hide();
            $('#insuranceValue').val('');
        }
    }

    function showHideCod() {
        if($("#collectOnDelivery").is(':checked')) {
            document.getElementById('collectOnDeliveryValueDiv').style.display = "";
        } else {
            $('#collectOnDeliveryValueDiv').hide();
            $('#collectOnDeliveryValue').val('');
        }
    }

    function showHideSas() {
        if ($("#deliveryToNeighbour").length > 0) {
            if($("#deliveryToNeighbour").is(':checked')) {
                document.getElementById('deliveryToNeighbourDiv').style.display = "";
            } else {
                $('#deliveryToNeighbourDiv').hide();
                $('#neighbour_name').val('');
                $('#neighbour_postcode').val('');
                $('#neighbour_city').val('');
                $('#neighbour_street').val('');
                $('#neighbour_houseNumber').val('');
                $('#neighbour_apartmentNumber').val('');
                $('#neighbour_phoneNumber').val('');
                $('#neighbour_emailAddress').val('');

            }
        }

    }
    function showHideLm() {
        if ($("#deliveryToLM").length > 0) {
            if($("#deliveryToLM").is(':checked')) {
                document.getElementById('deliveryToLMDiv').style.display = "";
            } else {
                $('#deliveryToLMDiv').hide();
                $('#lm_sap').val('');
            }
        }
    }

    function manageCountry() {
        if ($('#ek_country').val() == 'DE') {
            $('#label_ek_postfiliale').html('Postfiliale');
            document.getElementById('div_ek_packstation').style.display = "";
            document.getElementById('div_ek_postnummer').style.display = "";
            document.getElementById('ex_all_block').style.display = "";
            document.getElementById('search_link_paczkomat').style.display = "";
            document.getElementById('search_link_parcelshop').style.display = "";
        } else if ($('#ek_country').val() == 'DK') {
            $('#label_ek_postfiliale').html('Nr Parcelshop *');
            $('#div_ek_packstation').hide();
            $('#div_ek_postnummer').hide();
            document.getElementById('ex_all_block').style.display = "";
            $('#search_link_paczkomat').hide();
            document.getElementById('search_link_parcelshop').style.display = "";
        } else {
            $('#ex_all_block').hide();
            $('#search_link_paczkomat').hide();
            $('#search_link_parcelshop').hide();
        }
    }
    function changeCountry() {
        $('#ek_packstation').val('');
        $('#ek_postfiliale').val('');
        $('#ek_postnummer').val('');
        $('#mapType').val('');
        manageCountry();

    }


    /*  MECHANIZMY DO OBSŁUGI ZARZĄDZANIA PACZKAMI NA FORMULARZY TWORZENIA PRZESYŁKI */

    function nowy_wiersz_z_danymi(packageType, width, height, length, weight, quantity, nonStandard) {
        removeElementsByClass('wiersz_paczki');

        licznikPaczek = 1;
        var id_wiersza = 'wiersz_'+licznikPaczek;
        var tabela = document.getElementById('tabelaPaczek').getElementsByTagName('tbody')[0];

        var row = document.createElement("tr");
        row.id = id_wiersza;
        row.className = 'wiersz_paczki';
        // create table cell 1
        var td1 = document.createElement("td");
        var wiersz = '<select name="Package['+licznikPaczek+'][packageType]" id="packageType_'+licznikPaczek+'" class="text packageType">'
        if (packageType == 'ENVELOPE') {
            wiersz += '<option selected="selected" value="ENVELOPE">koperta</option>';
        } else {
            wiersz += '<option value="ENVELOPE">koperta</option>';
        }
        if (packageType == 'PACKAGE') {
            wiersz += '<option selected="selected" value="PACKAGE">paczka</option>';
        } else {
            wiersz += '<option value="PACKAGE">paczka</option>';
        }
        if (packageType == 'PALLET') {
            wiersz += '<option selected="selected" value="PALLET">paleta</option>';
        } else {
            wiersz += '<option value="PALLET">paleta</option>';
        }
        wiersz += '</select>';
        td1.innerHTML = wiersz;

        var td2 = document.createElement("td");
        td2.className = 'waga_' + licznikPaczek;
        if (packageType == 'ENVELOPE') {
            wiersz = 'Max 1kg';
        } else {
            wiersz = '<input name="Package['+licznikPaczek+'][weight]" id="weight_'+licznikPaczek+'" class="text" style="width: 40px;" value="' + weight +'" /> kg';
        }
        td2.innerHTML = wiersz;

        var td3 = document.createElement("td");
        td3.className = 'wymiary_'+licznikPaczek;
        if (packageType == 'ENVELOPE') {
            wiersz = 'Nie dotyczy';
        } else {
            wiersz = wypelnione_pola_do_wymiarow(licznikPaczek, width, height, length);
        }
        td3.innerHTML = wiersz;

        var td4 = document.createElement("td");
        td4.innerHTML = '<input name="Package['+licznikPaczek+'][quantity]" id="quantity_'+licznikPaczek+'" class="text" style="width: 40px;" value="' + quantity + '" />';

        var td5 = document.createElement("td");
        td5.className = 'niestandardowa_'+licznikPaczek;
        if (packageType == 'ENVELOPE') {
            wiersz = '';
        } else {
            if (nonStandard) {
                wiersz = '<input type="checkbox" name="Package['+licznikPaczek+'][nonStandard]" checked="checked" id="nonStandard_'+licznikPaczek+'" value="1" />';
            } else {
                wiersz = '<input type="checkbox" name="Package['+licznikPaczek+'][nonStandard]" id="nonStandard_'+licznikPaczek+'" value="1" />';
            }

        }
        td5.innerHTML = wiersz;

        var td6 = document.createElement("td");
        td6.innerHTML = '';
        row.appendChild(td1);
        row.appendChild(td2);
        row.appendChild(td3);
        row.appendChild(td4);
        row.appendChild(td5);
        row.appendChild(td6);
        tabela.appendChild(row);
    }

    function removeElementsByClass(className){
       // $('.' + className).remove();
        var elements = document.getElementsByClassName(className);
        while(elements.length > 0){
            elements[0].parentNode.removeChild(elements[0]);
        }
    }

    function nowy_wiersz() {
        licznikPaczek++;
        var id_wiersza = 'wiersz_'+licznikPaczek;
        var tabela = document.getElementById('tabelaPaczek').getElementsByTagName('tbody')[0];//
        var row = document.createElement("tr");
        row.id = id_wiersza;
        row.className = 'wiersz_paczki';
        var td1 = document.createElement("td");
        td1.innerHTML = '<select name="Package['+licznikPaczek+'][packageType]" id="packageType_'+licznikPaczek+'" class="text packageType"><option value="ENVELOPE">koperta</option><option value="PACKAGE" selected="selected">paczka</option><option value="PALLET">paleta</option></select>';
        var td2 = document.createElement("td");
        td2.className = 'waga_' + licznikPaczek;
        td2.innerHTML = '<input name="Package['+licznikPaczek+'][weight]" id="weight_'+licznikPaczek+'" class="text" style="width: 40px;" /> kg';
        var td3 = document.createElement("td");
        td3.className = 'wymiary_'+licznikPaczek;
        td3.innerHTML = puste_pola_do_wymiarow(licznikPaczek);
        var td4 = document.createElement("td");
        td4.innerHTML = '<input name="Package['+licznikPaczek+'][quantity]" id="quantity_'+licznikPaczek+'" class="text" style="width: 40px;" value="1" />';
        var td5 = document.createElement("td");
        td5.className = 'niestandardowa_'+licznikPaczek;
        td5.innerHTML = '<input type="checkbox" name="Package['+licznikPaczek+'][nonStandard]" id="nonStandard_'+licznikPaczek+'" class="" value="1" />';
        var td6 = document.createElement("td");
        td6.innerHTML = '<a href="javascript:;" class="remove" >Usuń</a>';
        row.appendChild(td1);
        row.appendChild(td2);
        row.appendChild(td3);
        row.appendChild(td4);
        row.appendChild(td5);
        row.appendChild(td6);
        tabela.appendChild(row);
    }
    function usun_wiersz(elem) {
        $(elem).parent().parent().remove();
    }

    function przelaczanie_typu_paczki( object ) {
        var id_wiersza = podaj_identyfikator_wiersza( object.id );
        var poprzednio_byly_pola = document.querySelectorAll(".wymiary_"+id_wiersza)[0].innerHTML;
        if( poprzednio_byly_pola.trim() == 'Nie dotyczy' ) {
            poprzednio_byly_pola = false;
        } else {
            poprzednio_byly_pola = true;
        }

        if( object.value == 'ENVELOPE' ) {
            document.querySelectorAll(".wymiary_"+id_wiersza)[0].innerHTML = 'Nie dotyczy';

        } else {
            if( !poprzednio_byly_pola ) {
                document.querySelectorAll(".wymiary_"+id_wiersza)[0].innerHTML = puste_pola_do_wymiarow( id_wiersza )
            }
        }

        if( object.value == 'ENVELOPE' ) {
            document.querySelectorAll(".waga_"+id_wiersza)[0].innerHTML = 'Max 1kg';
        } else {
            if( !poprzednio_byly_pola ) {
                document.querySelectorAll(".waga_"+id_wiersza)[0].innerHTML = puste_pole_do_wagi( id_wiersza )
            }
        }

        if( object.value == 'ENVELOPE' ) {
            document.querySelectorAll(".niestandardowa_"+id_wiersza)[0].innerHTML = '';
        } else {
            document.querySelectorAll(".niestandardowa_"+id_wiersza)[0].innerHTML = puste_niestandardowa( id_wiersza ) ;
        }
    }

    function wypelnione_pola_do_wymiarow( id_wiersza, width, height, length ) {
        var result =  '<input name="Package['+id_wiersza+'][width]" id="width_'+id_wiersza+'" class="text" style="width: 40px;" value="' + width + '" /> x ';
        result += '<input name="Package['+id_wiersza+'][height]" id="height_'+id_wiersza+'" class="text" style="width: 40px;" value="' + height + '" /> x ';
        result += '<input name="Package['+id_wiersza+'][length]" id="length_'+id_wiersza+'" class="text" style="width: 40px;" value="' + length + '" /> cm </td>';
        return result;
    }

    function puste_pola_do_wymiarow( id_wiersza ) {
        var result =  '<input name="Package['+id_wiersza+'][width]" id="width_'+id_wiersza+'" class="text" style="width: 40px;" /> x ';
        result += '<input name="Package['+id_wiersza+'][height]" id="height_'+id_wiersza+'" class="text" style="width: 40px;" /> x ';
        result += '<input name="Package['+id_wiersza+'][length]" id="length_'+id_wiersza+'" class="text" style="width: 40px;" /> cm </td>';
        return result;
    }

    function puste_pole_do_wagi( id_wiersza ) {
        var result = '<input name="Package['+id_wiersza+'][weight]" id="weight_'+id_wiersza+'" class="text" style="width: 40px;" /> kg';
        return result;
    }

    function puste_niestandardowa( id_wiersza ) {
        var result = '<input type="checkbox" name="Package['+id_wiersza+'][nonStandard] id="nonStandard_'+id_wiersza+'" class=""  value="1" />';
        return result;
    }

    function podaj_identyfikator_wiersza( string ) {
        var result = string.split( '_' );
        return result[1];
    }

});
