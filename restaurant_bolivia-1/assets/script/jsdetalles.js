function Separador(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function pulsar(e, valor) {
    tecla = (document.all) ? e.keyCode : e.which;
    if (tecla == 13) comprueba(valor)
}

$(document).ready(function() {

    $('#AgregaVenta').click(function() {
        AgregaVentas();
    });

    $('.agregaventa').keypress(function(e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {
          AgregaVentas();
          e.preventDefault();
          return false;
      }
  });

    function AgregaVentas () {

            var caja = $('input#codcaja').val();
            var code = $('input#codproducto').val();
            var prod = $('input#producto').val();
            var cantp = $('input#cantidad').val();
            var exist = $('input#existencia').val();
            var prec = $('input#preciocompra').val();
            var prec2 = $('input#precioventa').val();
            var descuen = $('input#descproducto').val();
            var ivgprod = $('input#ivaproducto').val();
            var er_num = /^([0-9])*[.]?[0-9]*$/;
            cantp = parseInt(cantp);
            exist = parseInt(exist);
            cantp = cantp;

            if (caja == "") {
                swal("Oops", "POR FAVOR DEBE DE REALIZAR EL ARQUEO DE CAJA, PARA PROCESAR VENTAS!", "error");
                return false;

            } else if (code == "") {
                $("#busquedaproductov").focus();
                $("#busquedaproductov").css('border-color', '#ff7676');
                swal("Oops", "POR FAVOR REALICE LA BÚSQUEDA DEL PRODUCTO CORRECTAMENTE!", "error");
                return false;

            } else if ($('#cantidad').val() == "" || $('#cantidad').val() == "0") {
                $("#cantidad").focus();
                $("#cantidad").css('border-color', '#ff7676');
                swal("Oops", "POR FAVOR INGRESE UNA CANTIDAD VÁLIDA EN VENTAS!", "error");
                return false;

            } else if (isNaN($('#cantidad').val())) {
                $("#cantidad").focus();
                $("#cantidad").css('border-color', '#ff7676');
                swal("Oops", "POR FAVOR INGRESE SOLO DIGITOS EN CANTIDAD DE VENTAS!", "error");
                return false;
                
           } else if(cantp > exist){
                $("#cantidad").focus();
                $('#cantidad').css('border-color','#ff7676');
                $("#existencia").focus();
                $('#existencia').css('border-color','#ff7676');
                swal("Oops", "LA CANTIDAD DE PRODUCTOS SOLICITADA NO EXISTE EN ALMACEN, VERIFIQUE NUEVAMENTE POR FAVOR!", "error");
                return false;

            } else {

            var Carrito = new Object();
            Carrito.Codigo = $('input#codproducto').val();
            Carrito.Producto = $('input#producto').val();
            Carrito.Codcategoria = $('input#codcategoria').val();
            Carrito.Categorias = $('input#categorias').val();
            Carrito.Precio      = $('input#preciocompra').val();
            Carrito.Precio2      = $('input#precioventa').val();
            Carrito.Descproducto      = $('input#descproducto').val();
            Carrito.Ivaproducto = $('input#ivaproducto').val();
            Carrito.Existencia = $('input#existencia').val();
            Carrito.Precioconiva = $('input#precioconiva').val();
            Carrito.Observacion = $('input#observacion').val();
            Carrito.Cantidad = $('input#cantidad').val();
            Carrito.opCantidad = '+=';
            var DatosJson = JSON.stringify(Carrito);
            $.post('carritoventa.php', {
                    MiCarrito: DatosJson
                },
            function(data, textStatus) {
                $("#carrito tbody").html("");
                var TotalDescuento = 0;
                var SubtotalFact = 0;
                var BaseImpIva1 = 0;
                var contador = 0;
                var iva = 0;
                var total = 0;
                var TotalCompra = 0;

                $.each(data, function(i, item) {
                    var cantsincero = item.cantidad;
                    cantsincero = parseInt(cantsincero);
                    if (cantsincero != 0) {
                        contador = contador + 1;

            var OperacionCompra= parseFloat(item.precio) * parseFloat(item.cantidad);
            TotalCompra = parseFloat(TotalCompra) + parseFloat(OperacionCompra);

             //CALCULO DEL VALOR TOTAL
            var ValorTotal= parseFloat(item.precio2) * parseFloat(item.cantidad);

            //CALCULO DEL TOTAL DEL DESCUENTO %
            var Descuento = ValorTotal * item.descproducto / 100;
            TotalDescuento = parseFloat(TotalDescuento) + parseFloat(Descuento);

            //OBTENEMOS DESCUENTO INDIVIDUAL POR PRODUCTOS
            var descsiniva = item.precio2 * item.descproducto / 100;
            var descconiva = item.precioconiva * item.descproducto / 100;

            //CALCULO DE BASE IMPONIBLE IVA SIN PORCENTAJE
            var Operac= parseFloat(item.precio2) - parseFloat(descsiniva);
            var Operacion= parseFloat(Operac) * parseFloat(item.cantidad);
            var Subtotal = Operacion.toFixed(2);

            //CALCULO DE BASE IMPONIBLE IVA CON PORCENTAJE
            var Operac3 = parseFloat(item.precioconiva) - parseFloat(descconiva);
            var Operacion3 = parseFloat(Operac3) * parseFloat(item.cantidad);
            var Subbaseimponiva = Operacion3.toFixed(2);

            //BASE IMPONIBLE IVA CON PORCENTAJE
            BaseImpIva1 = parseFloat(BaseImpIva1) + parseFloat(Subbaseimponiva);
            
            //CALCULO GENERAL DE IVA CON BASE IVA * IVA %
            var ivg = $('input#iva').val();
            ivg2  = ivg/100;
            TotalIvaGeneral = parseFloat(BaseImpIva1) * parseFloat(ivg2.toFixed(2));
            
            //SUBTOTAL GENERAL DE FACTURA
            SubtotalFact = parseFloat(SubtotalFact) + parseFloat(Subtotal);
            //BASE IMPONIBLE IVA SIN PORCENTAJE
            BaseImpIva2 = parseFloat(SubtotalFact) - parseFloat(BaseImpIva1);
            
            //CALCULAMOS DESCUENTO POR PRODUCTO
            var desc = $('input#descuento').val();
            desc2  = desc/100;
            
            //CALCULO DEL TOTAL DE FACTURA
            Total = parseFloat(BaseImpIva1) + parseFloat(BaseImpIva2) + parseFloat(TotalIvaGeneral);
            TotalDescuentoGeneral   = parseFloat(Total.toFixed(2)) * parseFloat(desc2.toFixed(2));
            TotalFactura   = parseFloat(Total.toFixed(2)) - parseFloat(TotalDescuentoGeneral.toFixed(2));

            var nuevaFila =
                "<tr class='warning-element text-center' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>" +
                "<td>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'-1'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'-'" +
                ')"' +
                " type='button'><span class='fa fa-minus'></span></button>" +
                "<input type='text' id='" + item.cantidad + "' class='bold' style='width:26px;height:34px;border:#ff7676;' value='" + item.cantidad + "'>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'+1'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'+'" +
                ')"' +
                " type='button'><span class='fa fa-plus'></span></button></td>" +
                "<td><strong>" + item.txtCodigo + "</strong></td>" +
                "<td class='text-left'><h5><strong>" + item.producto + "</strong></h5><small>(" + (item.codcategoria == '' || item.codcategoria == '0' ? '******' : item.categorias) + ")</small></td>" +
                "<td>" + Separador(item.precio2) + "</td>" +
                "<td>" + Separador(ValorTotal.toFixed(2)) + "</td>" +
                "<td>" + Separador(Descuento.toFixed(2)) + "<sup><strong>" + item.descproducto + "%</strong></sup></td>" +
                "<td>" + item.ivaproducto + "</td>" +
                "<td>" + Separador(Operacion.toFixed(2)) + "</td>" +
                "<td>" +
                '<button class="btn btn-dark btn-xs" style="cursor:pointer;border-radius:5px 5px 5px 5px;color:#fff;" ' +
                'onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'0'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'='" +
                ')"' +
                ' type="button"><span class="fa fa-trash-o"></span></button>' +
                "</td>" +
                "</tr>";
                
                $(nuevaFila).appendTo("#carrito tbody");
                            
                    $("#lblsubtotal").text(Separador(BaseImpIva1.toFixed(2)));
                    $("#lblsubtotal2").text(Separador(BaseImpIva2.toFixed(2)));
                    $("#lbliva").text(Separador(TotalIvaGeneral.toFixed(2)));
                    $("#lbldescontado").text(Separador(TotalDescuento.toFixed(2)));
                    $("#lbldescuento").text(Separador(TotalDescuentoGeneral.toFixed(2)));
                    $("#lbltotal").text(Separador(TotalFactura.toFixed(2)));
                    
                    $("#txtsubtotal").val(BaseImpIva1.toFixed(2));
                    $("#txtsubtotal2").val(BaseImpIva2.toFixed(2));
                    $("#txtIva").val(TotalIvaGeneral.toFixed(2));
                    $("#txtdescontado").val(TotalDescuento.toFixed(2));
                    $("#txtDescuento").val(TotalDescuentoGeneral.toFixed(2));
                    $("#txtTotal").val(TotalFactura.toFixed(2));
                    $("#txtTotalCompra").val(TotalCompra.toFixed(2));

                    /*####### ACTIVAR BOTON DE PAGO #######*/
                    $("#buttonpago").attr('disabled', false);
                    $("#TextImporte").text(Separador(TotalFactura.toFixed(2)));
                    $("#TextPagado").text(Separador(TotalFactura.toFixed(2)));
                    $("#montopagado").val(TotalFactura.toFixed(2));

                    }
                });

                $("#busquedaproductov").focus();
                LimpiarTexto();
            },
            "json"
        );
        return false;
    }
}

/* CANCELAR LOS ITEM AGREGADOS EN AGREGAR DETALLES */
$("#vaciar").click(function() {
        var Carrito = new Object();
        Carrito.Codigo = "vaciar";
        Carrito.Producto = "vaciar";
        Carrito.Codcategoria = "vaciar";
        Carrito.Categorias = "vaciar";
        Carrito.Precio      = "0";
        Carrito.Precio2      = "0";
        Carrito.Descproducto      = "0";
        Carrito.Ivaproducto = "vaciar";
        Carrito.Existencia = "vaciar";
        Carrito.Precioconiva  = "0";
        Carrito.Observacion   = "vaciar";
        Carrito.Cantidad = "0";
        var DatosJson = JSON.stringify(Carrito);
        $.post('carritoventa.php', {
                MiCarrito: DatosJson
            },
            function(data, textStatus) {
                $("#carrito tbody").html("");
                var nuevaFila =
                "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=9><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
                $(nuevaFila).appendTo("#carrito tbody");
                LimpiarTexto();
            },
            "json"
        );
        return false;
    });

$(document).ready(function() {
    $('#vaciar').click(function() {
        $("#carrito tbody").html("");
        var nuevaFila =
        "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=9><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
        $(nuevaFila).appendTo("#carrito tbody");
        $("#agregaventas")[0].reset();
        $("#codcliente").val("0");
        $("#lblsubtotal").text("0.00");
        $("#lblsubtotal2").text("0.00");
        $("#lbliva").text("0.00");
        $("#lbldescontado").text("0.00");
        $("#lbldescuento").text("0.00");
        $("#lbltotal").text("0.00");

        $("#txtsubtotal").val("0.00");
        $("#txtsubtotal2").val("0.00");
        $("#txtIva").val("0.00");
        $("#txtdescontado").val("0.00");
        $("#txtDescuento").val("0.00");
        $("#txtTotal").val("0.00");
    });
});


//FUNCION PARA ACTUALIZAR CALCULO EN FACTURA DE COMPRAS CON DESCUENTO
$(document).ready(function(){
    $('#descuento').keyup(function(){
        
        var txtsubtotal = $('input#txtsubtotal').val();
        var txtsubtotal2 = $('input#txtsubtotal2').val();
        var txtIva = $('input#txtIva').val();
        var desc = $('input#descuento').val();
        descuento  = desc/100;

        var opcionpropina = $('input:radio[name=opcionpropina]:checked').val();
        //var porcentaje = ($('input#propinasugerida').val() == 0.00 ? $('input#porcentajepropina').val() : $('input#propinasugerida').val()); 
        var totalpropina = $('input#totalpropina').val(); 
        var montopropinasugerida = $('input#montopropinasugerida').val();
        var montodelivery = $('input#montodelivery').val();
        var montopagado = $('input#montopagado').val();
        var montopagado2 = $('input#montopagado2').val();
        var montodevuelto = $('input#montodevuelto').val(); 
        var montopropina = $('input#montopropina').val();
        var sumpagado = parseFloat(montopagado) + parseFloat(montopagado2);
                        
        //REALIZO EL CALCULO CON EL DESCUENTO INDICADO
        Subtotal = parseFloat(txtsubtotal) + parseFloat(txtsubtotal2) + parseFloat(txtIva); 
        TotalDescuentoGeneral   = parseFloat(Subtotal.toFixed(2)) * parseFloat(descuento.toFixed(2));
        TotalFactura   = parseFloat(Subtotal.toFixed(2)) - parseFloat(TotalDescuentoGeneral.toFixed(2));

        if (opcionpropina == null) {
        TotalGeneral   = parseFloat(TotalFactura) + parseFloat(montodelivery);
        } else if (opcionpropina == 1) {
        TotalGeneral   = parseFloat(TotalFactura) + parseFloat(totalpropina);
        } else if (opcionpropina == 2) {
        TotalGeneral   = parseFloat(TotalFactura);
        } else if (opcionpropina == 3) {
        TotalGeneral   = parseFloat(TotalFactura) + parseFloat(montopropinasugerida);
        }

        if (parseFloat(TotalGeneral) > parseFloat(sumpagado)) {
        total_cambio = parseFloat(sumpagado) - parseFloat(TotalGeneral);
        } else if (parseFloat(sumpagado) > parseFloat(TotalGeneral)) {
        total_cambio = parseFloat(sumpagado) - parseFloat(TotalGeneral); 
        }
        var original_cambio = parseFloat(total_cambio.toFixed(2));

                
        $("#lbldescuento").text(Separador(TotalDescuentoGeneral.toFixed(2)));
        $("#lbltotal").text(Separador(TotalFactura.toFixed(2)));
        $("#txtDescuento").val(TotalDescuentoGeneral.toFixed(2));
        $("#txtTotal").val(TotalFactura.toFixed(2));
        $("#txtImporte").val(TotalGeneral.toFixed(2));

        $("#TextImporte").text(Separador(TotalGeneral.toFixed(2)));
        $("#TextPagado").text(Separador(sumpagado.toFixed(2)));
        $("#TextCambio").text(Separador(original_cambio.toFixed(2)));
    });
});


function LimpiarTexto() {
    $("#busquedaproductov").val("");
    $("#codproducto").val("");
    $("#producto").val("");
    $("#codcategoria").val("");
    $("#categorias").val("");
    $("#preciocompra").val("");
    $("#precioventa").val("");
    $("#descproducto").val("");
    $("#ivaproducto").val("");
    $("#existencia").val("");
    $("#precioconiva").val("");
    $("#observacion").val("");
    $("#cantidad").val("");
}


$("#carrito tbody").on('keydown', 'input', function(e) {
    var element = $(this);
    var pvalue = element.val();
    var code = e.charCode || e.keyCode;
    var avalue = String.fromCharCode(code);
    var action = element.siblings('button').first().attr('onclick');
    var params;
    if (code !== 11 && /[^\d]/ig.test(avalue)) {
        e.preventDefault();
        return;
    }
    if (element.attr('data-proc') == '1') {
        return true;
    }
    element.attr('data-proc', '1');
    params = action.match(/\'([^\']+)\'/g).map(function(v) {
        return v.replace(/\'/g, '');
    });
    setTimeout(function() {
        if (element.attr('data-proc') == '1') {
            var value = element.val() || 0;
            addItem(
                params[0],
                value,
                params[2],
                params[3],
                params[4],
                params[5],
                params[6],
                params[7],
                params[8],
                params[9],
                params[11],
                '='
            );
            element.attr('data-proc', '0');
            }
        }, 300);
    });
});

//FUNCION PARA SELECCIONAR REPARTIDOR DE PEDIDO EXTERNO
function TipoPedido(tipopedido){

    var valor = $('input[name="tipopedido"]:checked').val(); 
    var montototal = $('input#txtTotal').val();
    var montodelivery = $('input#montodelivery').val();

    if (valor === "" || valor === true) {

    // deshabilitamos
    $("#repartidor").attr('disabled', true);
    $("#montodelivery").attr('disabled', true);
    $("#repartidor").val("");
    $("#montodelivery").val("0.00");

    } else if (valor === "INTERNO" || valor === true) {

    // habilitamos
    $("#repartidor").attr('disabled', true);
    $("#montodelivery").attr('disabled', true);
    $("#repartidor").val("");
    $("#montodelivery").val("0.00");

    } else if (valor === "EXTERNO" || valor === true) {

    // deshabilitamos
    $("#repartidor").attr('disabled', false);
    $("#montodelivery").attr('disabled', false);
    }
}

// FUNCION PARA MOSTRAR REPARTIDORES EN MODAL
function CargarRepartidores(){
    
var tipopedido = $('input:radio[name=tipopedido]:checked').val();

var dataString = 'BuscaRepartidores=si&tipopedido='+tipopedido;

    $.ajax({
        type: "GET",
            url: "detalles_delivery.php",
            data: dataString,
            success: function(response) {            
            $('#muestra_repartidores').empty();
            $('#muestra_repartidores').append(''+response+'').fadeIn("slow");                
        }
    });
}

// FUNCION PARA MOSTRAR OPCIONES DE PAGO DE PROPINA
function CargaOpcionPropina(){

var opcionpropina = $('input:radio[name=opcionpropina]:checked').val();
var porcentaje = ($('input#propinasugerida').val() == 0.00 ? $('input#porcentajepropina').val() : $('input#propinasugerida').val()); 
var totalporcentaje = $('input#totalporcentajepropina').val();
var montopropinasugerida = ($('input#montopropinasugerida').val() == 0.00 ? $('input#totalporcentajepropina').val() : $('input#montopropinasugerida').val()); 

var montototal = $('input#txtTotal').val();
var montofactura = $('input#txtFactura').val();
var montopagado = $('input#montopagado').val();
var montopagado2 = $('input#montopagado2').val();
var montodevuelto = $('input#montodevuelto').val(); 
var montopropina = $('input#montopropina').val();

if (opcionpropina == 1) {
            
    //REALIZO EL CALCULO
    $("#montopropinasugerida").attr('disabled', true);
    $('input#montopropinasugerida').val("0.00");
    var totalpropina = montofactura * porcentaje / 100;
    $('input#propinasugerida').val(porcentaje);
    $('input#totalpropina').val(totalpropina.toFixed(2));
    var sumtotal = parseFloat(montototal) + parseFloat(montopropina) + parseFloat(totalpropina);
    var Sumatoria = parseFloat(sumtotal.toFixed(2));

    var sumpagado = parseFloat(montopagado) + parseFloat(montopagado2);
    var subtotal= parseFloat(sumpagado) + parseFloat(montopropina);
    total = parseFloat(sumpagado) - parseFloat(sumtotal);
    var original = parseFloat(total.toFixed(2));

} else if (opcionpropina == 2) {
            
    //REALIZO EL CALCULO
    $("#montopropinasugerida").attr('disabled', true);
    $('input#montopropinasugerida').val("0.00");
    $('input#propinasugerida').val("0.00");
    $('input#totalpropina').val("0.00");
    var sumtotal = parseFloat(montototal) + parseFloat(montopropina);
    var Sumatoria = parseFloat(sumtotal.toFixed(2));

} else if (opcionpropina == 3) {
            
    //REALIZO EL CALCULO
    $("#montopropinasugerida").attr('disabled', false);
    $("input#montopropinasugerida").focus();
    $('input#propinasugerida').val("0.00");
    $('input#totalpropina').val("0.00");
    $("input#montopropinasugerida").val("0.00");
    var sumtotal = parseFloat(montototal) + parseFloat(montopropina) + parseFloat(montopropinasugerida);
    var Sumatoria = parseFloat(sumtotal.toFixed(2));
}

    $("#txtImporte").val(Sumatoria.toFixed(2));
    $("#TextImporte").text(Separador(Sumatoria.toFixed(2)));
    $("#TextPagado").text(Separador(Sumatoria.toFixed(2)));
    $("#montopagado").val(Sumatoria.toFixed(2));
}


//FUNCION PARA CALCULAR MONTO DEVOLUCION EN VENTA
function DevolucionVenta(){
      
    if ($('input#txtTotalPago').val()==0.00 || $('input#txtTotalPago').val()==0 || $('input#txtTotalPago').val()=="") {
              
        $("#montopagado").val("0.00");
        $("#montopagado2").val("0.00");
        swal("Oops", "POR FAVOR AGREGUE DETALLES PARA CONTINUAR CON LA VENTA DE PRODUCTOS!", "error");
        return false;
   
    } else {
      
    var opcionpropina = $('input:radio[name=opcionpropina]:checked').val();
    var montototal = $('input#txtTotal').val();
    var montodelivery = $('input#montodelivery').val();
    var montopagado = $('input#montopagado').val();
    var montopagado2 = $('input#montopagado2').val();
    var montodevuelto = $('input#montodevuelto').val(); 
    var totalpropina = $('input#totalpropina').val(); 
    var montopropinasugerida = $('input#montopropinasugerida').val();
            
    //REALIZO EL CALCULO Y MUESTRO LA DEVOLUCION
    if (opcionpropina == null) {
        var sumtotal = parseFloat(montototal) + parseFloat(montodelivery);
    } else if (opcionpropina == 1) {
        var sumtotal = parseFloat(montototal) + parseFloat(totalpropina);
    } else if (opcionpropina == 2) {
        var sumtotal = parseFloat(montototal);
    } else if (opcionpropina == 3) {
        var sumtotal = parseFloat(montototal) + parseFloat(montopropinasugerida);
    }
    //var sumtotal = parseFloat(montototal) + parseFloat(montodelivery);
    var Sumatoria = parseFloat(sumtotal.toFixed(2));

    var sumpagado = parseFloat(montopagado) + parseFloat(montopagado2);
    total = parseFloat(sumpagado) - parseFloat(sumtotal);
    var original = parseFloat(total.toFixed(2));

    $("#TextImporte").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(Sumatoria.toFixed(2)) : Separador(Sumatoria.toFixed(2)));
    $("#TextPagado").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(sumpagado.toFixed(2)) : Separador(sumpagado.toFixed(2)));
    $("#TextCambio").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(sumpagado.toFixed(2)) : Separador(original.toFixed(2)));
    $("#txtImporte").val((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Sumatoria.toFixed(2) : Sumatoria.toFixed(2));
    $("#montodevuelto").val((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? sumtotal.toFixed(2) : original.toFixed(2));
   }
}



//FUNCION PARA CALCULAR MONTO DEVOLUCION EN VENTA
function DevolucionVenta8888888(){
      
    if ($('input#txtTotalPago').val()==0.00 || $('input#txtTotalPago').val()==0 || $('input#txtTotalPago').val()=="") {
              
        $("#montopagado").val("0.00");
        $("#montopagado2").val("0.00");
        swal("Oops", "POR FAVOR AGREGUE DETALLES PARA CONTINUAR CON LA VENTA DE PRODUCTOS!", "error");
        return false;
   
    } else {
      
    var opcionpropina = $('input:radio[name=opcionpropina]:checked').val();
    var montototal = $('input#txtTotalPago').val();
    var montodelivery = $('input#montodelivery').val();
    var montopagado = $('input#montopagado').val();
    var montopagado2 = $('input#montopagado2').val();
    var montodevuelto = $('input#montodevuelto').val(); 
    var totalpropina = $('input#totalpropina').val(); 
    var montopropinasugerida = $('input#montopropinasugerida').val();
            
    //REALIZO EL CALCULO Y MUESTRO LA DEVOLUCION
    /*if (opcionpropina == null) {
        var sumtotal = parseFloat(montototal) + parseFloat(montodelivery);
    } else*/ if (opcionpropina == 1) {
        var sumtotal = parseFloat(montototal) + parseFloat(totalpropina);
    } else if (opcionpropina == 2) {
        var sumtotal = parseFloat(montototal);
    } else if (opcionpropina == 3) {
        var sumtotal = parseFloat(montototal) + parseFloat(montopropinasugerida);
    } else {
        var sumtotal = parseFloat(montototal) + parseFloat(montodelivery);
    }
    var Sumatoria = parseFloat(sumtotal.toFixed(2));

    var sumpagado = parseFloat(montopagado) + parseFloat(montopagado2);
    /*if (opcionpropina == null) {
        var sumtotal = parseFloat(sumpagado) + parseFloat(montodelivery);
    } else*/ if (opcionpropina == 1) {
        var subtotal= parseFloat(sumpagado) + parseFloat(totalpropina);
    } else if (opcionpropina == 2) {
        var subtotal= parseFloat(sumpagado);
    } else if (opcionpropina == 3) {
        var subtotal= parseFloat(sumpagado) + parseFloat(montopropinasugerida);
    } else {
        var sumtotal = parseFloat(sumpagado) + parseFloat(montodelivery);
    }
    
    total = parseFloat(sumpagado) - parseFloat(sumtotal);
    var original = parseFloat(total.toFixed(2));

    $("#TextImporte").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(Sumatoria.toFixed(2)) : Separador(Sumatoria.toFixed(2)));
    $("#TextPagado").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(sumtotal) : Separador(sumpagado.toFixed(2)));
    $("#TextCambio").text(total);
    //$("#TextCambio").text((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Separador(sumtotal) : Separador(original.toFixed(2)));
    $("#txtTotal").val((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? Sumatoria.toFixed(2) : Sumatoria.toFixed(2));
    $("#montodevuelto").val((montopagado == "" || montopagado == "0" || montopagado == "0.00") ? sumtotal : original.toFixed(2));
   }
}


//FUNCIONES PARA ACTIVAR-DESACTIVAR MONTO PAGO #2
$(document).ready(function(){
   $('#formapago2').on('change', function() {

    var two = $('select#formapago2').val();

        if (two != "" || two === true) {

        $("#montopagado2").attr('disabled', false);
        $("#montopagado2").focus();

        } else if (two == "" || two === true) {

        $("#montopagado2").attr('disabled', true);
        $("#montopagado2").val("0.00");

        } 
    });
});


function addItem(codigo, cantidad, producto, codcategoria, categorias, precio, precio2, descproducto, ivaproducto, existencia, precioconiva, observacion, opCantidad) {

    var Carrito = new Object();
    Carrito.Codigo = codigo;
    Carrito.Producto = producto;
    Carrito.Codcategoria = codcategoria;
    Carrito.Categorias = categorias;
    Carrito.Precio = precio;
    Carrito.Precio2 = precio2;
    Carrito.Descproducto = descproducto;
    Carrito.Ivaproducto = ivaproducto;
    Carrito.Existencia = existencia;
    Carrito.Precioconiva  = precioconiva;
    Carrito.Observacion   = observacion;
    Carrito.Cantidad = cantidad;
    Carrito.opCantidad = opCantidad;
    var DatosJson = JSON.stringify(Carrito);
    $.post('carritoventa.php', {
            MiCarrito: DatosJson
        },
    function(data, textStatus) {
        $("#carrito tbody").html("");
        var TotalDescuento = 0;
        var SubtotalFact = 0;
        var BaseImpIva1 = 0;
        var contador = 0;
        var iva = 0;
        var total = 0;
        var TotalCompra = 0;

        $.each(data, function(i, item) {
            var cantsincero = item.cantidad;
            cantsincero = parseInt(cantsincero);
            if (cantsincero != 0) {
                contador = contador + 1;

            var OperacionCompra= parseFloat(item.precio) * parseFloat(item.cantidad);
            TotalCompra = parseFloat(TotalCompra) + parseFloat(OperacionCompra);

             //CALCULO DEL VALOR TOTAL
            var ValorTotal= parseFloat(item.precio2) * parseFloat(item.cantidad);

            //CALCULO DEL TOTAL DEL DESCUENTO %
            var Descuento = ValorTotal * item.descproducto / 100;
            TotalDescuento = parseFloat(TotalDescuento) + parseFloat(Descuento);

            //OBTENEMOS DESCUENTO INDIVIDUAL POR PRODUCTOS
            var descsiniva = item.precio2 * item.descproducto / 100;
            var descconiva = item.precioconiva * item.descproducto / 100;

            //CALCULO DE BASE IMPONIBLE IVA SIN PORCENTAJE
            var Operac= parseFloat(item.precio2) - parseFloat(descsiniva);
            var Operacion= parseFloat(Operac) * parseFloat(item.cantidad);
            var Subtotal = Operacion.toFixed(2);

            //CALCULO DE BASE IMPONIBLE IVA CON PORCENTAJE
            var Operac3 = parseFloat(item.precioconiva) - parseFloat(descconiva);
            var Operacion3 = parseFloat(Operac3) * parseFloat(item.cantidad);
            var Subbaseimponiva = Operacion3.toFixed(2);

            //BASE IMPONIBLE IVA CON PORCENTAJE
            BaseImpIva1 = parseFloat(BaseImpIva1) + parseFloat(Subbaseimponiva);
            
            //CALCULO GENERAL DE IVA CON BASE IVA * IVA %
            var ivg = $('input#iva').val();
            ivg2  = ivg/100;
            TotalIvaGeneral = parseFloat(BaseImpIva1) * parseFloat(ivg2.toFixed(2));
            
            //SUBTOTAL GENERAL DE FACTURA
            SubtotalFact = parseFloat(SubtotalFact) + parseFloat(Subtotal);
            //BASE IMPONIBLE IVA SIN PORCENTAJE
            BaseImpIva2 = parseFloat(SubtotalFact) - parseFloat(BaseImpIva1);
            
            //CALCULAMOS DESCUENTO POR PRODUCTO
            var desc = $('input#descuento').val();
            desc2  = desc/100;
            
            //CALCULO DEL TOTAL DE FACTURA
            Total = parseFloat(BaseImpIva1) + parseFloat(BaseImpIva2) + parseFloat(TotalIvaGeneral);
            TotalDescuentoGeneral   = parseFloat(Total.toFixed(2)) * parseFloat(desc2.toFixed(2));
            TotalFactura   = parseFloat(Total.toFixed(2)) - parseFloat(TotalDescuentoGeneral.toFixed(2));

                var nuevaFila =
                "<tr class='warning-element text-center' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>" +
                "<td>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'-1'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'-'" +
                ')"' +
                " type='button'><span class='fa fa-minus'></span></button>" +
                "<input type='text' id='" + item.cantidad + "' class='bold' style='width:26px;height:34px;border:#ff7676;' value='" + item.cantidad + "'>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'+1'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'+'" +
                ')"' +
                " type='button'><span class='fa fa-plus'></span></button></td>" +
                "<td><strong>" + item.txtCodigo + "</strong></td>" +
                "<td class='text-left'><h5><strong>" + item.producto + "</strong></h5><small>(" + (item.codcategoria == '' || item.codcategoria == '0' ? '******' : item.categorias) + ")</small></td>" +
                "<td>" + Separador(item.precio2) + "</td>" +
                "<td>" + Separador(ValorTotal.toFixed(2)) + "</td>" +
                "<td>" + Separador(Descuento.toFixed(2)) + "<sup><strong>" + item.descproducto + "%</strong></sup></td>" +
                "<td>" + item.ivaproducto + "</td>" +
                "<td>" + Separador(Operacion.toFixed(2)) + "</td>" +
                "<td>" +
                '<button class="btn btn-dark btn-xs" style="cursor:pointer;border-radius:5px 5px 5px 5px;color:#fff;" ' +
                'onclick="addItem(' +
                "'" + item.txtCodigo + "'," +
                "'0'," +
                "'" + item.producto + "'," +
                "'" + item.codcategoria + "'," +
                "'" + item.categorias + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'" + item.descproducto + "', " +
                "'" + item.ivaproducto + "', " +
                "'" + item.existencia + "', " +
                "'" + item.precioconiva + "', " +
                "'" + item.observacion + "', " +
                "'='" +
                ')"' +
                ' type="button"><span class="fa fa-trash-o"></span></button>' +
                "</td>" +
                "</tr>";
                                
                $(nuevaFila).appendTo("#carrito tbody");
                                
                    $("#lblsubtotal").text(Separador(BaseImpIva1.toFixed(2)));
                    $("#lblsubtotal2").text(Separador(BaseImpIva2.toFixed(2)));
                    $("#lbliva").text(Separador(TotalIvaGeneral.toFixed(2)));
                    $("#lbldescontado").text(Separador(TotalDescuento.toFixed(2)));
                    $("#lbldescuento").text(Separador(TotalDescuentoGeneral.toFixed(2)));
                    $("#lbltotal").text(Separador(TotalFactura.toFixed(2)));

                    $("#txtsubtotal").val(BaseImpIva1.toFixed(2));
                    $("#txtsubtotal2").val(BaseImpIva2.toFixed(2));
                    $("#txtIva").val(TotalIvaGeneral.toFixed(2));
                    $("#txtdescontado").val(TotalDescuento.toFixed(2));
                    $("#txtDescuento").val(TotalDescuentoGeneral.toFixed(2));
                    $("#txtTotal").val(TotalFactura.toFixed(2));
                    $("#txtTotalCompra").val(TotalCompra.toFixed(2));

                    /*####### ACTIVAR BOTON DE PAGO #######*/
                    $("#buttonpago").attr('disabled', false);
                    $("#TextImporte").text(Separador(TotalFactura.toFixed(2)));
                    $("#TextPagado").text(Separador(TotalFactura.toFixed(2)));
                    $("#montopagado").val(TotalFactura.toFixed(2));
                }
            });
            if (contador == 0) {

                $("#carrito tbody").html("");

                var nuevaFila =
                "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=9><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
                $(nuevaFila).appendTo("#carrito tbody");

                //alert("ELIMINAMOS TODOS LOS SUBTOTAL Y TOTALES");
                $("#saveventas")[0].reset();
                $("#lblsubtotal").text("0.00");
                $("#lblsubtotal2").text("0.00");
                $("#lbliva").text("0.00");
                $("#lbldescontado").text("0.00");
                $("#lbldescuento").text("0.00");
                $("#lbltotal").text("0.00");
                
                $("#txtsubtotal").val("0.00");
                $("#txtsubtotal2").val("0.00");
                $("#txtIva").val("0.00");
                $("#txtdescontado").val("0.00");
                $("#txtDescuento").val("0.00");
                $("#txtTotal").val("0.00");
                $("#txtTotalCompra").val("0.00");

                /*####### ACTIVAR BOTON DE PAGO #######*/
                $("#buttonpago").attr('disabled', true);
                $("#TextImporte").text("0.00");
                $("#TextPagado").text("0.00");
                $("#montopagado").text("0.00");
            }
            LimpiarTexto();
        },
        "json"
    );
    return false;
}