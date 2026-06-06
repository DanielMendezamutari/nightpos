<?php
require_once("class/class.php");
?>
<script src="assets/script/jscalendario.js"></script>
<script src="assets/script/autocompleto.js"></script>

<?php
$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);
?>

<?php 
######################## MUESTRA CONDICIONES DE PAGO EN MESA ########################
if (isset($_GET['BuscaCondicionesPagosMesas']) && isset($_GET['tipopago']) && isset($_GET['txtTotalPago']) && isset($_GET['txtTotalPropina'])) { 
  
$tra = new Login();

if(limpiar($_GET['tipopago'])==""){ echo ""; 

 } elseif(limpiar($_GET['tipopago'])=="CONTADO"){  ?>

    <div class="row">

        
        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Propina Sugerida</h4><hr>
            
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="control-label">Desea Pagar?: <span class="symbol required"></span></label><br>
                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="1" name="opcionpropina" value="1" onClick="CargaOpcionPropina()" checked="checked">
                    <label class="custom-control-label" for="1">SI</label>
                  </div>
                </div>

                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="2" name="opcionpropina" value="2" onClick="CargaOpcionPropina()">
                    <label class="custom-control-label" for="2">NO</label>
                  </div>
                </div>

                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="3" name="opcionpropina" value="3" onClick="CargaOpcionPropina()">
                    <label class="custom-control-label" for="3">OTRO MONTO</label>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Propina Recibida: </label>
              <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montopropinasugerida" id="montopropinasugerida" onKeyUp="CargaOpcionPropina();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Propina Recibida" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
              <i class="fa fa-bars form-control-feedback"></i>
              <input type="hidden" name="montopropina" id="montopropina" value="0.00">
              <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO" selected="">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
              <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionVenta();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($_GET['txtTotalPago']+$_GET['txtTotalPropina'], 2, '.', ''); ?>" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 2: </label>
              <i class="fa fa-bars form-control-feedback"></i>
              <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" onchange="FormasPagos2();" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 2: </label>
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="totalpagado2" id="totalpagado2" onKeyUp="DevolucionVenta();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div>

      <div class="row">
        <div class="col-md-12"> 
          <div class="form-group has-feedback2"> 
            <label class="control-label">Observaciones: </label> 
            <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
            <i class="fa fa-comment-o form-control-feedback2"></i> 
          </div> 
        </div>
      </div> 
          
 <?php   } else if(limpiar($_GET['tipopago'])=="CREDITO"){  ?>

    <div class="row">
      <div class="col-md-4"> 
        <div class="form-group has-feedback"> 
          <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
          <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo date("d-m-Y"); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
          <i class="fa fa-calendar form-control-feedback"></i>  
        </div> 
      </div>

      <div class="col-md-4"> 
        <div class="form-group has-feedback"> 
          <label class="control-label">Forma de Abono: </label>
          <i class="fa fa-bars form-control-feedback"></i>
          <select style="color:#000;font-weight:bold;" name="medioabono" id="medioabono" class="form-control" required="" aria-required="true">
          <option value=""> -- SELECCIONE -- </option>
          <option value="EFECTIVO">EFECTIVO</option>
          <option value="CHEQUE">CHEQUE</option>
          <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
          <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
          <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
          <option value="TRANSFERENCIA">TRANSFERENCIA</option>
          <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
          <option value="CUPON">CUPÓN</option>
          <option value="OTROS">OTROS</option>
          </select>
        </div> 
      </div>

      <div class="col-md-4"> 
        <div class="form-group has-feedback"> 
          <label class="control-label">Abono Crédito: <span class="symbol required"></span></label>
          <input type="hidden" name="formapago" id="formapago" value="">
          <input type="hidden" name="totalpagado" id="totalpagado" value="0.00">
          <input type="hidden" name="formapago2" id="formapago2" value="">
          <input type="hidden" name="totalpagado2" id="totalpagado2" value="0.00">
          <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
          <input type="hidden" name="montopropina" id="montopropina" value="0.00">
          <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montoabono" id="montoabono" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Monto de Abono" value="0.00" required="" aria-required="true"> 
          <i class="fa fa-dollar form-control-feedback"></i>
        </div> 
      </div>
    </div>

    <div class="row">
      <div class="col-md-12"> 
        <div class="form-group has-feedback2"> 
          <label class="control-label">Observaciones: </label> 
          <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
          <i class="fa fa-comment-o form-control-feedback2"></i> 
        </div> 
      </div>
    </div>
 
<?php  
  }
}
######################## MUESTRA CONDICIONES DE PAGO EN MESA ########################
?>








<?php 
######################## MUESTRA CONDICIONES DE PAGO PARA DELIVERY ########################
if (isset($_GET['BuscaCondicionesPagosDelivery']) && isset($_GET['tipopago']) && isset($_GET['txtTotal'])) { 
  
$tra = new Login();

 if(limpiar($_GET['tipopago'])==""){ echo ""; 

 } elseif(limpiar($_GET['tipopago'])=="CONTADO"){  ?>

    <div class="row">

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
              <i class="fa fa-bars form-control-feedback"></i>
              <input type="hidden" name="montopropina" id="montopropina" value="0.00">
              <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO" selected="">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
              <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionDelivery();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($_GET['txtTotal'], 2, '.', ''); ?>" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 2: </label>
              <i class="fa fa-bars form-control-feedback"></i>
              <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" onchange="FormasPagos2();" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 2: </label>
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionDelivery();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div>
          
 <?php   } else if(limpiar($_GET['tipopago'])=="CREDITO"){  ?>

      <div class="row">
        <div class="col-md-4"> 
             <div class="form-group has-feedback"> 
                <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo date("d-m-Y"); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                <i class="fa fa-calendar form-control-feedback"></i>  
           </div> 
        </div>

        <div class="col-md-4"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Abono: </label>
                <i class="fa fa-bars form-control-feedback"></i>
                <select style="color:#000;font-weight:bold;" name="medioabono" id="medioabono" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
        </div>

        <div class="col-md-4"> 
          <div class="form-group has-feedback"> 
            <label class="control-label">Abono Crédito: <span class="symbol required"></span></label>
            <input type="hidden" name="formapago" id="formapago" value="">
            <input type="hidden" name="montopagado" id="montopagado" value="0.00">
            <input type="hidden" name="formapago2" id="formapago2" value="">
            <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
            <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
            <input type="hidden" name="montopropina" id="montopropina" value="0.00">
            <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montoabono" id="montoabono" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Monto de Abono" value="0.00" required="" aria-required="true"> 
            <i class="fa fa-dollar form-control-feedback"></i>
          </div> 
        </div>
      </div>
 
<?php  
  }
}
######################## MUESTRA CONDICIONES DE PAGO PARA DELIVERY ########################
?>










<?php 
######################## MUESTRA CONDICIONES DE PAGO PARA PEDIDO ########################
if (isset($_GET['BuscaCondicionesPagosPedidos']) && isset($_GET['tipopago']) && isset($_GET['txtTotal'])) { 
  
$tra = new Login();

 if(limpiar($_GET['tipopago'])==""){ echo ""; 

 } elseif(limpiar($_GET['tipopago'])=="CONTADO"){  ?>

    <div class="row">

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
              <i class="fa fa-bars form-control-feedback"></i>
              <input type="hidden" name="montopropina" id="montopropina" value="0.00">
              <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO" selected="">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
              <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($_GET['txtTotal'], 2, '.', ''); ?>" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 2: </label>
              <i class="fa fa-bars form-control-feedback"></i>
              <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" onchange="FormasPagos2();" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 2: </label>
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div>
          
 <?php   } else if(limpiar($_GET['tipopago'])=="CREDITO"){  ?>

      <div class="row">
        <div class="col-md-4"> 
             <div class="form-group has-feedback"> 
                <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo date("d-m-Y"); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                <i class="fa fa-calendar form-control-feedback"></i>  
           </div> 
        </div>

        <div class="col-md-4"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Abono: </label>
                <i class="fa fa-bars form-control-feedback"></i>
                <select style="color:#000;font-weight:bold;" name="medioabono" id="medioabono" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
        </div>

        <div class="col-md-4"> 
          <div class="form-group has-feedback"> 
            <label class="control-label">Abono de Pedido: <span class="symbol required"></span></label>
            <input type="hidden" name="formapago" id="formapago" value="">
            <input type="hidden" name="montopagado" id="montopagado" value="0.00">
            <input type="hidden" name="formapago2" id="formapago2" value="">
            <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
            <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
            <input type="hidden" name="montopropina" id="montopropina" value="0.00">
            <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montoabono" id="montoabono" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Monto de Abono" value="0.00" required="" aria-required="true"> 
            <i class="fa fa-dollar form-control-feedback"></i>
          </div> 
        </div>
      </div>
 
<?php  
  }
}
######################## MUESTRA CONDICIONES DE PAGO PARA PEDIDO ########################
?>












<?php 
######################## MUESTRA CONDICIONES DE PAGO PARA COTIZACIONES ########################
if (isset($_GET['BuscaCondicionesPagosCotizaciones']) && isset($_GET['tipopago']) && isset($_GET['txtTotal'])) { 
  
 if(limpiar($_GET['tipopago'])==""){ echo ""; 

 } elseif(limpiar($_GET['tipopago'])=="CONTADO"){  ?>

    <div class="row">

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
              <i class="fa fa-bars form-control-feedback"></i>
              <input type="hidden" name="montopropina" id="montopropina" value="0.00">
              <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO" selected="">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
              <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionCotizacion();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($_GET['txtTotal'], 2, '.', ''); ?>" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 2: </label>
              <i class="fa fa-bars form-control-feedback"></i>
              <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" onchange="FormasPagos2();" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 2: </label>
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionCotizacion();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div><!-- END CONDICION PAGO -->

      <div class="row">
        <div class="col-md-12"> 
          <div class="form-group has-feedback2"> 
            <label class="control-label">Observaciones: </label> 
            <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
            <i class="fa fa-comment-o form-control-feedback2"></i> 
          </div> 
        </div>
      </div>
          
 <?php   } else if(limpiar($_GET['tipopago'])=="CREDITO"){  ?>

      <div class="row">
        <div class="col-md-6"> 
             <div class="form-group has-feedback"> 
                <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo date("d-m-Y"); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                <i class="fa fa-calendar form-control-feedback"></i>  
           </div> 
        </div>

        <div class="col-md-6"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Abono: </label>
                <i class="fa fa-bars form-control-feedback"></i>
                <select style="color:#000;font-weight:bold;" name="medioabono" id="medioabono" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
        </div>
      </div>

      <div class="row">
        <div class="col-md-6"> 
          <div class="form-group has-feedback"> 
            <label class="control-label">Abono Crédito: <span class="symbol required"></span></label>
            <input type="hidden" name="formapago" id="formapago" value="">
            <input type="hidden" name="montopagado" id="montopagado" value="0.00">
            <input type="hidden" name="formapago2" id="formapago2" value="">
            <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
            <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
            <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montoabono" id="montoabono" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Monto de Abono" value="0.00" required="" aria-required="true"> 
            <i class="fa fa-dollar form-control-feedback"></i>
          </div> 
        </div>

        <div class="col-md-6"> 
          <div class="form-group has-feedback2"> 
            <label class="control-label">Observaciones: </label> 
            <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
            <i class="fa fa-comment-o form-control-feedback2"></i> 
          </div> 
        </div>
      </div>
 
<?php  
   }
}
######################## MUESTRA CONDICIONES DE PAGO PARA COTIZACIONES ########################
?>