//MASCARAS PARA LOS INPUT TEXT
$(document).ready(function() {
    $(".phone-inputmask").mask("9999 9999999");
    $(".date-inputmask").mask("dd/mm/yyyy"); 
    $(".international-inputmask").mask("+9(999)999-9999"); 
    $(".xphone-inputmask").mask("(999) 999-9999 / x999999"); 
    $(".purchase-inputmask").mask("aaaa 9999-****"); 
    $(".cc-inputmask").mask("9999 9999 9999 9999"); 
    $(".ssn-inputmask").mask("999-99-9999"); 
    $(".isbn-inputmask").mask("999-99-999-9999-9"); 
    $(".currency-inputmask").mask("$9999"); 
    $(".percentage-inputmask").mask("99%"); 
    $(".decimal-inputmask").mask({
        alias: "decimal"
        , radixPoint: "."
    });
});