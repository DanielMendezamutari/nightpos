function Separador(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function pulsar(e, valor) {
    tecla = (document.all) ? e.keyCode : e.which;
    if (tecla == 13) comprueba(valor)
}

$(document).ready(function() {

    $('#AgregaIngrediente').click(function() {
        AgregaIngredientes();
    });

    $('.agregaingrediente').keypress(function(e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {
          AgregaIngredientes();
          e.preventDefault();
          return false;
      }
  });

    function AgregaIngredientes () {

        var code = $('input#codingrediente').val();
        var prod = $('input#search_ingrediente_sucursal').val();
        var cantp = $('input#cantidad').val();
        var prec = $('input#preciocompraing').val();
        var prec2 = $('input#precioventaing').val();
        var er_num = /^([0-9])*[.]?[0-9]*$/;
        cantp = parseInt(cantp);
        cantp = cantp;

        if (code == "") {
            $("#search_ingrediente_sucursal").focus();
            $("#search_ingrediente_sucursal").css('border-color', '#f0ad4e');
            swal("Oops", "POR FAVOR REALICE LA BÚSQUEDA DEL INGREDIENTE CORRECTAMENTE!", "error");
            return false;
        

        } else if ($('#cantidad').val() == "" || $('#cantidad').val() == "0") {
            $("#cantidad").focus();
            $("#cantidad").css('border-color', '#f0ad4e');
            swal("Oops", "POR FAVOR INGRESE UNA CANTIDAD VÁLIDA!", "error");
            return false;

        } else if (isNaN($('#cantidad').val())) {
            $("#cantidad").focus();
            $("#cantidad").css('border-color', '#f0ad4e');
            swal("Oops", "POR FAVOR INGRESE SOLO DIGITOS EN CANTIDAD!", "error");
            return false;
            
        } else {

        var Carrito = new Object();
        Carrito.Id = $('input#idingrediente').val();
        Carrito.Codigo = $('input#codingrediente').val();
        Carrito.Producto = $('input#search_ingrediente_sucursal').val();
        Carrito.Medida = $('input#medida').val();
        Carrito.Precio      = $('input#preciocompraing').val();
        Carrito.Precio2      = $('input#precioventaing').val();
        Carrito.Cantidad = $('input#cantidad').val();
        Carrito.opCantidad = '+=';
        var DatosJson = JSON.stringify(Carrito);
        $.post('carritoingrediente.php', {
                MiCarrito: DatosJson
            },
        function(data, textStatus) {
        $("#carrito tbody").html("");
        var TotalDescuento = 0;
        var PrecioCompra = 0;
        var PrecioVenta = 0;
        var contador = 0;

            $.each(data, function(i, item) {
                var cantsincero = item.cantidad;
                cantsincero = parseFloat(cantsincero);
                if (cantsincero != 0) {
                    contador = contador + 1;

            //CALCULO DEL PRECIO COMPRA
            var TotalCompra= parseFloat(item.precio) * parseFloat(item.cantidad);
            PrecioCompra = parseFloat(PrecioCompra) + parseFloat(TotalCompra);
            var OperacionCompra= parseFloat(item.precio) * parseFloat(item.cantidad);

            //CALCULO DEL PRECIO VENTA
            var TotalVenta= parseFloat(item.precio2) * parseFloat(item.cantidad);
            PrecioVenta = parseFloat(PrecioVenta) + parseFloat(TotalVenta);
            var OperacionVenta= parseFloat(item.precio2) * parseFloat(item.cantidad);

                var nuevaFila =
                "<tr class='warning-element text-center' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>" +
                "<td>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'-0.5'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'-'" +
                ')"' +
                " type='button'><span class='fa fa-minus'></span></button>" +
                "<input type='text' id='" + item.cantidad + "' class='bold' style='width:40px;height:34px;border:#f9d655;' value='" + item.cantidad + "'>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'+0.5'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'+'" +
                ')"' +
                " type='button'><span class='fa fa-plus'></span></button></td>" +
                "<td align='left'><h5><strong>" + item.producto + "</strong></h5></td>" +
                "<td><strong>" + item.medida + "</strong></td>" +
                "<td><strong>" + Separador(OperacionCompra.toFixed(2)) + "</strong></td>" +
                "<td><strong>" + Separador(OperacionVenta.toFixed(2)) + "</strong></td>" +
                "<td>" +
                '<button class="btn btn-dark btn-xs btn-rounded" style="cursor:pointer;color:#fff;" ' +
                'onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'0'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'='" +
                ')"' +
                ' type="button"><span class="fa fa-trash-o"></span></button>' +
                "</td>" +
                "</tr>";
                        $(nuevaFila).appendTo("#carrito tbody");
                        //$("#preciocompra").val(PrecioCompra.toFixed(2));
                        //$("#precioventa").val(PrecioVenta.toFixed(2));
                    }
                });

                $("#search_ingrediente_sucursal").focus();
                LimpiarTexto();
            },
            "json"
        );
        return false;
    }
}

/* CANCELAR LOS ITEM AGREGADOS EN REGISTRO */
$("#vaciar").click(function() {
    var Carrito = new Object();
    Carrito.Id = "vaciar";
    Carrito.Codigo = "vaciar";
    Carrito.Producto = "vaciar";
    Carrito.Medida = "vaciar";
    Carrito.Precio      = "vaciar";
    Carrito.Precio2      = "0.00";
    Carrito.Cantidad = "0";
    var DatosJson = JSON.stringify(Carrito);
    $.post('carritoingrediente.php', {
            MiCarrito: DatosJson
        },
        function(data, textStatus) {
            $("#carrito tbody").html("");
            var nuevaFila =
            "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=6><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
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
        "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=6><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
        $(nuevaFila).appendTo("#carrito tbody");
        //$("#preciocompra").val("");
        //$("#precioventa").val("");
    });
});

function LimpiarTexto() {
    $("#search_ingrediente_sucursal").val("");
    $("#idingrediente").val("");
    $("#codingrediente").val("");
    $("#medida").val("");
    $("#preciocompraing").val("");
    $("#precioventaing").val("");
    $("#cantidad").val("0.5");
}


$("#carrito tbody").on('keydown', 'input', function(e) {
        var element = $(this);
        var pvalue = element.val();
        /*var code = e.charCode || e.keyCode;
        var avalue = String.fromCharCode(code);*/
        var regx = /^[A-Za-z0-9 _.-]+$/;
        var action = element.siblings('button').first().attr('onclick');
        var params;
        //if (code !== 11 && /[^\d]/ig.test(avalue)) {
        if (!regx.test(e.charCode) || !regx.test(e.keyCode)){
            e.preventDefault();
            return;
        }

        if (element.attr('data-proc') == '0.5') {
            return true;
        }
        element.attr('data-proc', '0.5');
        params = action.match(/\'([^\']+)\'/g).map(function(v) {
            return v.replace(/\'/g, '');
        });
        setTimeout(function() {
            if (element.attr('data-proc') == '0.5') {
                var value = element.val() || 0;
                addItem(
                    params[0],
                    params[1],
                    value,
                    params[2],
                    params[3],
                    params[4],
                    params[5],
                    '='
                );
                element.attr('data-proc', '0');
            }
        }, 300);
    });
});


function addItem(id, codigo, cantidad, producto, medida, precio, precio2, opCantidad) {
    var Carrito = new Object();
    Carrito.Id = id;
    Carrito.Codigo = codigo;
    Carrito.Producto = producto;
    Carrito.Medida = medida;
    Carrito.Precio = precio;
    Carrito.Precio2 = precio2;
    Carrito.Cantidad = cantidad;
    Carrito.opCantidad = opCantidad;
    var DatosJson = JSON.stringify(Carrito);
    $.post('carritoingrediente.php', {
            MiCarrito: DatosJson
        },
        function(data, textStatus) {
            $("#carrito tbody").html("");
            var TotalDescuento = 0;
            var PrecioCompra = 0;
            var PrecioVenta = 0;
            var contador = 0;

            $.each(data, function(i, item) {
                var cantsincero = item.cantidad;
                cantsincero = parseFloat(cantsincero);
                if (cantsincero != 0) {
                    contador = contador + 1;

                //CALCULO DEL PRECIO COMPRA
                var TotalCompra= parseFloat(item.precio) * parseFloat(item.cantidad);
                PrecioCompra = parseFloat(PrecioCompra) + parseFloat(TotalCompra);
                var OperacionCompra= parseFloat(item.precio) * parseFloat(item.cantidad);

                //CALCULO DEL PRECIO VENTA
                var TotalVenta= parseFloat(item.precio2) * parseFloat(item.cantidad);
                PrecioVenta = parseFloat(PrecioVenta) + parseFloat(TotalVenta);
                var OperacionVenta= parseFloat(item.precio2) * parseFloat(item.cantidad);

                var nuevaFila =
                "<tr class='warning-element text-center' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>" +
                "<td>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'-0.5'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'-'" +
                ')"' +
                " type='button'><span class='fa fa-minus'></span></button>" +
                "<input type='text' id='" + item.cantidad + "' class='bold' style='width:40px;height:34px;border:#f9d655;' value='" + item.cantidad + "'>" +
                '<button class="btn btn-xs" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'+0.5'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'+'" +
                ')"' +
                " type='button'><span class='fa fa-plus'></span></button></td>" +
                "<td align='left'><h5><strong>" + item.producto + "</strong></h5></td>" +
                "<td><strong>" + item.medida + "</strong></td>" +
                "<td><strong>" + Separador(OperacionCompra.toFixed(2)) + "</strong></td>" +
                "<td><strong>" + Separador(OperacionVenta.toFixed(2)) + "</strong></td>" +
                "<td>" +
                '<button class="btn btn-dark btn-xs btn-rounded" style="cursor:pointer;color:#fff;" ' +
                'onclick="addItem(' +
                "'" + item.id + "'," +
                "'" + item.txtCodigo + "'," +
                "'0'," +
                "'" + item.producto + "'," +
                "'" + item.medida + "'," +
                "'" + item.precio + "', " +
                "'" + item.precio2 + "', " +
                "'='" +
                ')"' +
                ' type="button"><span class="fa fa-trash-o"></span></button>' +
                            "</td>" +
                            "</tr>";
                $(nuevaFila).appendTo("#carrito tbody");
                    //$("#preciocompra").val(PrecioCompra.toFixed(2));
                    //$("#precioventa").val(PrecioVenta.toFixed(2));             
                }
            });
            if (contador == 0) {

                $("#carrito tbody").html("");

                var nuevaFila =
                "<tr class='warning-element' style='border-left: 2px solid #ffb22b !important; background: #fefde3;'>"+"<td class='text-center' colspan=6><h4>NO HAY DETALLES AGREGADOS</h4></td>"+"</tr>";
                $(nuevaFila).appendTo("#carrito tbody");
                //$("#preciocompra").val("");
                //$("#precioventa").val("");
            }
            LimpiarTexto();
        },
        "json"
    );
    return false;
}