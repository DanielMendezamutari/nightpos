// FUNCION AUTOCOMPLETE 
$(function() {  
    var animales = ["Ardilla roja", "Gato", "Gorila occidental",  
      "Leon", "Oso pardo", "Perro", "Tigre de Bengala"];  
      
    $("#prueba").autocomplete({  
      source: animales  
    });  
});

$(function() {
       $("#categorias").autocomplete({
       source: "class/busqueda_autocompleto.php?Busqueda_Categorias=si",
       minLength: 1,
       select: function(event, ui) { 
       $('#codcategoria').val(ui.item.codcategoria);
       }  
    });
});


$(function() {
    $("#busqueda").autocomplete({
       source: "class/busqueda_autocompleto.php?Busqueda_Clientes=si",
       minLength: 1,
       select: function(event, ui) { 
       $('#codcliente').val(ui.item.codcliente);
       $('#nrodocumento').val(ui.item.dnicliente);
       $('#creditoinicial').val(ui.item.limitecredito);
       $('#montocredito').val(ui.item.creditodisponible);
       $('#creditodisponible').val(ui.item.creditodisponible);
       $('#TextCliente').text((ui.item.tipocliente == "JURIDICO") ? ui.item.razoncliente : ui.item.nomcliente);
       $('#TextCredito').text(ui.item.creditodisponible);
       }  
    });
});


/* FUNCION AUTOCOMPLETE DE BUSQUEDA DE CLIENTE POR SUCURSAL*/
$(function() {
    $("#search_cliente_sucursal").autocomplete({
        minLength: 1,
        source: "class/busqueda_autocompleto.php?Busqueda_Clientes_Sucursal=si",
        //source: '@Url.Action("FillSelectedSeriesName")',
        data: { series_id: $("#codsucursal option:selected").text() },
        //data: { series_id: $("#SeriesName option:selected").text(), series_name: document.getElementById("SeriesName").value },
        source: function (request, response) {
            var term = request.term;
            var term2 = document.getElementById("codsucursal").value;
            //var series_name = $("#SeriesName option:selected").text();
            if(term2 == ""){
               swal("Oops", "POR FAVOR DEBE SELECCIONAR UNA SUCURSAL!", "warning");
                return false;
            }
            $.getJSON("class/busqueda_autocompleto.php?Busqueda_Clientes_Sucursal=si?term=" + term + '&term2=' + term2, request, function (data, status, xhr) {
                response(data);
            });
        },
        select: function (event, ui) {
            $('#codcliente').val(ui.item.codcliente);
            $('#nrodocumento').val(ui.item.dnicliente);
        },
        change: function (event, ui) {
        }
    });
});


$(function() {
    $("#search_cliente1").autocomplete({
       source: "class/busqueda_autocompleto.php?Busqueda_Clientes=si",
       minLength: 1,
       select: function(event, ui) { 
       $('#cobrarmesa #codcliente').val(ui.item.codcliente);
       $('#cobrarmesa #nrodocumento').val(ui.item.dnicliente);
       $('#cobrarmesa #creditoinicial').val(ui.item.limitecredito);
       $('#cobrarmesa #montocredito').val(ui.item.creditodisponible);
       $('#cobrarmesa #creditodisponible').val(ui.item.creditodisponible);
       $('#cobrarmesa #TextCliente').text((ui.item.tipocliente == "JURIDICO") ? ui.item.razoncliente : ui.item.nomcliente);
       $('#cobrarmesa #TextCredito').text(ui.item.creditodisponible);
       }  
    });
});

$(function() {
    $("#search_cliente2").autocomplete({
       source: "class/busqueda_autocompleto.php?Busqueda_Clientes=si",
       minLength: 1,
       select: function(event, ui) { 
       $('#cobrarmesaseparada #codcliente').val(ui.item.codcliente);
       $('#cobrarmesaseparada #nrodocumento').val(ui.item.dnicliente);
       $('#cobrarmesaseparada #creditoinicial').val(ui.item.limitecredito);
       $('#cobrarmesaseparada #montocredito').val(ui.item.creditodisponible);
       $('#cobrarmesaseparada #creditodisponible').val(ui.item.creditodisponible);
       $('#cobrarmesaseparada #TextCliente').text((ui.item.tipocliente == "JURIDICO") ? ui.item.razoncliente : ui.item.nomcliente);
       $('#cobrarmesaseparada #TextCredito').text(ui.item.creditodisponible);
       }  
    });
});

/* FUNCION AUTOCOMPLETE DE BUSQUEDA DE INGREDIENTES POR SUCURSAL*/
$(function() {
    $("#search_ingrediente_sucursal").autocomplete({
        minLength: 1,
        source: "class/busqueda_autocompleto.php?Busqueda_Ingredientes_Sucursal=si",
        data: { series_id: $("#codsucursal option:selected").text() },
        source: function (request, response) {
            var term = request.term;
            var term2 = document.getElementById("codsucursal").value;
            if(term2 == ""){
               swal("Oops", "POR FAVOR DEBE SELECCIONAR UNA SUCURSAL!", "warning");
                return false;
            }
            $.getJSON("class/busqueda_autocompleto.php?Busqueda_Ingredientes_Sucursal=si?term=" + term + '&term2=' + term2, request, function (data, status, xhr) {
                response(data);
            });
        },
        select: function (event, ui) {
            $('#idingrediente').val(ui.item.idingrediente);
            $('#codingrediente').val(ui.item.codingrediente);
            $('#nomingrediente').val(ui.item.nomingrediente);
            $('#codmedida').val(ui.item.codmedida);
            $('#medida').val(ui.item.nommedida);
            $('#preciocompraing').val(ui.item.preciocompra);
            $('#precioventaing').val(ui.item.precioventa);
            $('#precioconiva').val((ui.item.ivaingrediente == "SI") ? ui.item.precioventa : "0.00");
            $('#cantingrediente').val(ui.item.cantingrediente);
            $('#ivaingrediente').val((ui.item.ivaingrediente == "SI") ? ui.item.ivaingrediente : "0.00");
            $('#descingrediente').val(ui.item.descingrediente);
            $('#fechaexpiracion').val((ui.item.fechaexpiracion == "0000-00-00") ? "" : ui.item.fechaexpiracion);
            $("#cantidad").focus();
        },
        change: function (event, ui) {
        }
    });
});


/* FUNCION AUTOCOMPLETE DE BUSQUEDA DE PRODUCTO POR SUCURSAL*/
$(function() {
    $("#search_producto_sucursal").autocomplete({
        minLength: 1,
        source: "class/busqueda_autocompleto.php?Busqueda_Productos_Sucursal=si",
        data: { series_id: $("#codsucursal option:selected").text() },
        source: function (request, response) {
            var term = request.term;
            var term2 = document.getElementById("codsucursal").value;
            if(term2 == ""){
               swal("Oops", "POR FAVOR DEBE SELECCIONAR UNA SUCURSAL!", "warning");
                return false;
            }
            $.getJSON("class/busqueda_autocompleto.php?Busqueda_Productos_Sucursal=si?term=" + term + '&term2=' + term2, request, function (data, status, xhr) {
                response(data);
            });
        },
        select: function (event, ui) {
            $('#idproducto').val(ui.item.idproducto);
            $('#codproducto').val(ui.item.codproducto);
            $('#producto').val(ui.item.producto);
            $('#codcategoria').val(ui.item.codcategoria);
            $('#categoria').val(ui.item.nomcategoria);
            $('#preciocompradet').val(ui.item.preciocompra);
            $('#precioventadet').val(ui.item.precioventa);
            $('#precioconiva').val((ui.item.ivaproducto == "SI") ? ui.item.precioventa : "0.00");
            $('#ivaproducto').val((ui.item.ivaproducto == "SI") ? ui.item.ivaproducto : "0.00");
            $('#descproducto').val(ui.item.descproducto);
            $('#preparado').val(ui.item.preparado);
            $('#tipo').val("1");
            $("#cantidad").focus();
        },
        change: function (event, ui) {
        }
    });
});


/* FUNCION AUTOCOMPLETE DE BUSQUEDA DE COMBOS POR SUCURSAL*/
$(function() {
    $("#search_combo_sucursal").autocomplete({
        minLength: 1,
        source: "class/busqueda_autocompleto.php?Busqueda_Combos_Sucursal=si",
        data: { series_id: $("#codsucursal option:selected").text() },
        source: function (request, response) {
            var term = request.term;
            var term2 = document.getElementById("codsucursal").value;
            if(term2 == ""){
               swal("Oops", "POR FAVOR DEBE SELECCIONAR UNA SUCURSAL!", "warning");
                return false;
            }
            $.getJSON("class/busqueda_autocompleto.php?Busqueda_Combos_Sucursal=si?term=" + term + '&term2=' + term2, request, function (data, status, xhr) {
                response(data);
            });
        },
        select: function (event, ui) {
            $('#idcombo').val(ui.item.idcombo);
            $('#codcombo').val(ui.item.codcombo);
        },
        change: function (event, ui) {
        }
    });
});


// FUNCION AUTOCOMPLETE PARA COMPRAS
$(function() {

    $("#search_compra").keyup(function() {

        var tipo = $('input:radio[name=tipo]:checked').val(); 

        if (tipo == "") {

            $("#tipo").focus();
            $('#tipo').css('border-color', '#2cabe3');
            $("#search_compra").val("");
            swal("Oops", "POR FAVOR SELECCIONE EL TIPO DE BUSQUEDA!", "error");
            return false;

        } else if (tipo == 1) {

            $("#search_compra").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Productos=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idproducto);
                $('#codproducto').val(ui.item.codproducto);
                $('#producto').val(ui.item.producto);
                $('#codcategoria').val(ui.item.codcategoria);
                $('#categorias').val(ui.item.nomcategoria);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaproducto == "SI") ? ui.item.preciocompra : "0.00");
                $('#existencia').val(ui.item.existencia);
                $('#ivaproducto').val(ui.item.ivaproducto);
                $('#descproducto').val(ui.item.descproducto);
                $("#cantidad").focus();
            }
          });

          return false;

        } else if (tipo == 2) {

            $("#search_compra").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Ingredientes=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idingrediente);
                $('#codproducto').val(ui.item.codingrediente);
                $('#producto').val(ui.item.nomingrediente);
                $('#codcategoria').val(ui.item.codmedida);
                $('#categorias').val(ui.item.nommedida);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaingrediente == "SI") ? ui.item.preciocompra : "0.00");
                $('#existencia').val(ui.item.cantingrediente);
                $('#ivaproducto').val(ui.item.ivaingrediente);
                $('#descproducto').val(ui.item.descingrediente);
                $("#cantidad").focus();
            }
        });

        }
    });
}); 



// FUNCION AUTOCOMPLETE PARA TRASPASOS
$(function() {

    $("#search_traspaso").keyup(function() {

        var tipo = $('input:radio[name=tipo]:checked').val(); 

        if (tipo == "") {

            $("#tipo").focus();
            $('#tipo').css('border-color', '#2cabe3');
            $("#search_traspaso").val("");
            swal("Oops", "POR FAVOR SELECCIONE EL TIPO DE BUSQUEDA!", "error");
            return false;

        } else if (tipo == 1) {

            $("#search_traspaso").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Productos=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idproducto);
                $('#codproducto').val(ui.item.codproducto);
                $('#producto').val(ui.item.producto);
                $('#codcategoria').val(ui.item.codcategoria);
                $('#categorias').val(ui.item.nomcategoria);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaproducto == "SI") ? ui.item.precioventa : "0.00");
                $('#existencia').val(ui.item.existencia);
                $('#ivaproducto').val((ui.item.ivaproducto == "SI") ? ui.item.ivaproducto : "0.00");
                $('#descproducto').val(ui.item.descproducto);
                $('#lote').val(ui.item.lote);
                $('#fechaelaboracion').val(ui.item.fechaelaboracion);
                $('#fechaexpiracion').val(ui.item.fechaexpiracion);
                $("#cantidad").focus();
            }
        });

        return false;

        } else if (tipo == 3) {

            $("#search_traspaso").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Ingredientes=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idingrediente);
                $('#codproducto').val(ui.item.codingrediente);
                $('#producto').val(ui.item.nomingrediente);
                $('#codcategoria').val(ui.item.codmedida);
                $('#categorias').val(ui.item.nommedida);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaingrediente == "SI") ? ui.item.precioventa : "0.00");
                $('#existencia').val(ui.item.cantingrediente);
                $('#ivaproducto').val((ui.item.ivaingrediente == "SI") ? ui.item.ivaingrediente : "0.00");
                $('#descproducto').val(ui.item.descingrediente);
                $('#lote').val(ui.item.lote);
                $('#fechaelaboracion').val("0000-00-00");
                $('#fechaexpiracion').val(ui.item.fechaexpiracion);
                $("#cantidad").focus();
            }
        });

        }
    });
}); 



// FUNCION AUTOCOMPLETE PARA COTIZACIONES
$(function() {

    $("#search_cotizacion").keyup(function() {

        var tipo = $('input:radio[name=tipo]:checked').val(); 

        if (tipo == "") {

            $("#tipo").focus();
            $('#tipo').css('border-color', '#2cabe3');
            $("#search_cotizacion").val("");
            swal("Oops", "POR FAVOR SELECCIONE EL TIPO DE BUSQUEDA!", "error");
            return false;

        } else if (tipo == 1) {

            $("#search_cotizacion").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Productos=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idproducto);
                $('#codproducto').val(ui.item.codproducto);
                $('#producto').val(ui.item.producto);
                $('#codcategoria').val(ui.item.codcategoria);
                $('#categorias').val(ui.item.nomcategoria);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaproducto == "SI") ? ui.item.precioventa : "0.00");
                $('#existencia').val(ui.item.existencia);
                $('#ivaproducto').val((ui.item.ivaproducto == "SI") ? ui.item.ivaproducto : "0.00");
                $('#descproducto').val(ui.item.descproducto);
                $('#preparado').val(ui.item.preparado);
                $("#cantidad").focus();
            }
        });

        return false;

        } else if (tipo == 2) {

            $("#search_cotizacion").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Combos=si",
            minLength: 1,
            select: function(event, ui) {
                $('#idproducto').val(ui.item.idcombo);
                $('#codproducto').val(ui.item.codcombo);
                $('#producto').val(ui.item.nomcombo);
                $('#codcategoria').val("**********");
                $('#categorias').val("**********");
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivacombo == "SI") ? ui.item.precioventa : "0.00");
                $('#existencia').val(ui.item.existencia);
                $('#ivaproducto').val((ui.item.ivacombo == "SI") ? ui.item.ivacombo : "0.00");
                $('#descproducto').val(ui.item.desccombo);
                $('#preparado').val(ui.item.preparado);
                $("#cantidad").focus();
            }
        });

        return false;

        } else if (tipo == 3) {

            $("#search_cotizacion").autocomplete({
            source: "class/busqueda_autocompleto.php?Busqueda_Ingredientes=si",
            minLength: 1,
            select: function(event, ui) {

                $('#idproducto').val(ui.item.idingrediente);
                $('#codproducto').val(ui.item.codingrediente);
                $('#producto').val(ui.item.nomingrediente);
                $('#codcategoria').val(ui.item.codmedida);
                $('#categorias').val(ui.item.nommedida);
                $('#preciocompra').val(ui.item.preciocompra);
                $('#precioventa').val(ui.item.precioventa);
                $('#precioconiva').val((ui.item.ivaingrediente == "SI") ? ui.item.precioventa : "0.00");
                $('#existencia').val(ui.item.cantingrediente);
                $('#ivaproducto').val((ui.item.ivaingrediente == "SI") ? ui.item.ivaingrediente : "0.00");
                $('#descproducto').val(ui.item.descingrediente);
                $('#preparado').val(ui.item.preparado);
                $("#cantidad").focus();
            }
        });

        }
    });
}); 


/* FUNCION AUTOCOMPLETE DE BUSQUEDA DE COMBOS POR SUCURSAL*/
$(function() {
    $("#numfactura").autocomplete({
        minLength: 1,
        source: "class/busqueda_autocompleto.php?Busqueda_Facturas=si",
        data: { series_id: $("#codsucursal option:selected").text() },
        source: function (request, response) {
            var term = request.term;
            var term2 = document.getElementById("codsucursal").value;
            if(term2 == ""){
               swal("Oops", "POR FAVOR DEBE SELECCIONAR UNA SUCURSAL!", "warning");
                return false;
            }
            $.getJSON("class/busqueda_autocompleto.php?Busqueda_Facturas=si?term=" + term + '&term2=' + term2, request, function (data, status, xhr) {
                response(data);
            });
        },
        select: function (event, ui) {
            $('#numeroventa').val(ui.item.codventa);
        },
        change: function (event, ui) {
        }
    });
});