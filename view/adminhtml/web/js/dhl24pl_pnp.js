var licznikPaczek = 0;
'use strict';
require(['jquery'], function($) {
    $(document).ready(function() {
        init();
    });

    function init(){
        $( "#saveButton" ).click(function() {
            $('#pnpForm').submit();
        });
        changeKind();
        $( "#kind" ).change(function() {
            changeKind();
        });
    }
    function changeKind(){
        if ($('#kind').val() == 'WEBAPI') {
            $('#typeRow').show();
        } else {
            $('#typeRow').hide();
        }
    }


});
