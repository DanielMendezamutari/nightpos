/*$(function () {
   $('input').filter('.expira').datepicker({*/

$('body').on('focus',".expira", function(){
   $(this).datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     //firstDay: 1,
     minDate: 0,
    //maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: new Date().getFullYear() + ':2050'
  });
$('#ui-datepicker-div').appendTo($('.modal'));
});


$('body').on('focus',"#fechaautorsucursal", function(){
   $(this).datepicker({
    closeText: 'Cerrar',
    prevText: '<Anterior',
    nextText: 'Siguiente>',
    currentText: 'Hoy',
    monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
    dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
    weekHeader: 'Sm',
    dateFormat: 'dd-mm-yy',
    firstDay: 1,
    maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: '1900:' + new Date().getFullYear()
  });
$('#ui-datepicker-div').appendTo($('.modal'));
});


$('body').on('focus',".calendario", function(){
   $(this).datepicker({
    closeText: 'Cerrar',
    prevText: '<Anterior',
    nextText: 'Siguiente>',
    currentText: 'Hoy',
    monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
    dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
    weekHeader: 'Sm',
    dateFormat: 'dd-mm-yy',
    firstDay: 1,
    maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: '1900:' + new Date().getFullYear()
  });
});


$('body').on('focus',".calendario2", function(){
   $(this).datepicker({
    closeText: 'Cerrar',
    prevText: '<Anterior',
    nextText: 'Siguiente>',
    currentText: 'Hoy',
    monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
    dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
    weekHeader: 'Sm',
    dateFormat: 'dd-mm-yy',
    firstDay: 1,
    maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: '1900:' + new Date().getFullYear()
  });
$('#ui-datepicker-div').appendTo($('.modal'));
});


$('body').on('focus',"#fechavencecredito", function(){
   $(this).datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     //firstDay: 1,
     minDate: 0,
    //maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: new Date().getFullYear() + ':2050'
  });
});


$(function () {
    $.datepicker.setDefaults($.datepicker.regional["es"]);
    $("#fechaoptimo").datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     //firstDay: 1,
     minDate: 0,
    //maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: new Date().getFullYear() + ':2050'
  });
});

$(function () {
    $.datepicker.setDefaults($.datepicker.regional["es"]);
    $("#fechamedio").datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     //firstDay: 1,
     minDate: 0,
    //maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: new Date().getFullYear() + ':2050'
  });
});

$(function () {
    $.datepicker.setDefaults($.datepicker.regional["es"]);
    $("#fechaminimo").datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     //firstDay: 1,
     minDate: 0,
    //maxDate: 0,
    changeMonth: true,
    changeYear: true,
    yearRange: new Date().getFullYear() + ':2050'
  });
});


$(function () {
  $.datepicker.setDefaults($.datepicker.regional["es"]);
    $("#desde").datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     firstDay: 1,
     isRTL: false,
     showMonthAfterYear: false,
     changeMonth: true,
     changeYear: true,
     yearSuffix: '',
     onClose: function (selectedDate) {
      $("#hasta").datepicker("option", "minDate", selectedDate);
    }
  });

$("#hasta").datepicker({
     closeText: 'Cerrar',
     prevText: '<Anterior',
     nextText: 'Siguiente>',
     currentText: 'Hoy',
     monthNamesShort: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNames: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
     dayNamesShort: ['Dom','Lun','Mar','Mie','Juv','Vie','Sab'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     firstDay: 1,
     isRTL: false,
     showMonthAfterYear: false,
     changeMonth: true,
     changeYear: true,
     yearSuffix: '',
     onClose: function (selectedDate) {
      $("#desde").datepicker("option", "maxDate", selectedDate);
    }
  });
});

$('body').on('focus',"#hora_desde", function(){
    $(this).timepicker({showMeridian: false});
});

$('body').on('focus',"#hora_hasta", function(){
  $(this).timepicker({showMeridian: false});
});