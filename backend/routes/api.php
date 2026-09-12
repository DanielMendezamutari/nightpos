<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SalonMesaController;
use App\Http\Controllers\Api\V1\ComandaController;
use App\Http\Controllers\Api\V1\CajaFacturaController;
use App\Http\Controllers\Api\V1\QrPagoController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\CompraController;
use App\Http\Controllers\Api\V1\ProveedorController;
use App\Http\Controllers\Api\V1\RecetaController;
use App\Http\Controllers\Api\V1\KdsController;
use App\Http\Controllers\Api\V1\GastoController;
use App\Http\Controllers\Api\V1\SepararCuentasController;
use App\Http\Controllers\Api\V1\MesaOperacionesController;
use App\Http\Controllers\Api\V1\ReporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'product' => 'RiberResto POS',
            'company' => 'Ribersoft',
            'timestamp' => now()->toISOString(),
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('login-pin', [AuthController::class, 'loginPin']);
        Route::post('login-password', [AuthController::class, 'loginPassword']);

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::prefix('salones')->group(function () {
        Route::get('/', [SalonMesaController::class, 'getSalones']);
        Route::post('/', [SalonMesaController::class, 'storeSalon']);
        Route::put('{salonId}', [SalonMesaController::class, 'updateSalon']);
        Route::get('{salonId}/mesas', [SalonMesaController::class, 'getMesasBySalon']);
    });

    Route::get('visitas/{visitaId}', [MesaOperacionesController::class, 'getVisita']);
    Route::post('visitas/{visitaId}/comanda', [ComandaController::class, 'agregarComandaVisita']);
    Route::post('visitas/{visitaId}/cobrar-facturar', [CajaFacturaController::class, 'cobrarYFacturarVisita']);

    Route::prefix('mesas')->group(function () {
        Route::post('/', [SalonMesaController::class, 'storeMesa']);
        Route::put('{mesaId}', [SalonMesaController::class, 'updateMesa']);
        Route::delete('{mesaId}', [SalonMesaController::class, 'deleteMesa']);
        Route::get('{mesaId}', [SalonMesaController::class, 'getMesaDetails']);
        Route::post('{mesaId}/abrir', [SalonMesaController::class, 'abrirMesa']);
        Route::post('{mesaId}/cambiar', [SalonMesaController::class, 'cambiarMesa']);
        Route::post('{mesaId}/precuenta', [SalonMesaController::class, 'solicitarPrecuenta']);
        Route::post('{mesaId}/liberar', [SalonMesaController::class, 'liberarMesa']);
        Route::post('{mesaId}/comanda', [ComandaController::class, 'agregarComanda']);
        Route::post('{mesaId}/cobrar-facturar', [CajaFacturaController::class, 'cobrarYFacturar']);
    });

    Route::prefix('menu')->group(function () {
        Route::get('categorias', [ComandaController::class, 'getCategorias']);
        Route::get('productos', [ComandaController::class, 'getProductos']);
        Route::get('observaciones-cocina', [ComandaController::class, 'getObservacionesCocina']);
    });

    Route::delete('visita-detalles/{detalleId}', [ComandaController::class, 'eliminarItem']);

    Route::prefix('caja')->group(function () {
        Route::get('turno-activo', [CajaFacturaController::class, 'getTurnoActivo']);
        Route::post('abrir-turno', [CajaFacturaController::class, 'abrirTurno']);
        Route::post('cerrar-turno', [CajaFacturaController::class, 'cerrarTurno']);
        Route::get('movimientos', [CajaFacturaController::class, 'getMovimientos']);
        Route::post('movimientos', [CajaFacturaController::class, 'registrarMovimiento']);
        Route::post('arqueo-ciego', [CajaFacturaController::class, 'realizarArqueoCiego']);
        Route::get('reporte-cierre/{turnoId}', [CajaFacturaController::class, 'getReporteCierreZ']);

        // Gastos operativos y egresos de caja chica (frmGastos RestoTech)
        Route::get('gastos/tipos', [GastoController::class, 'getTiposGastos']);
        Route::post('gastos/tipos', [GastoController::class, 'storeTipoGasto']);
        Route::get('gastos', [GastoController::class, 'index']);
        Route::post('gastos', [GastoController::class, 'store']);
        Route::delete('gastos/{id}', [GastoController::class, 'anular']);
    });

    Route::prefix('facturas')->group(function () {
        Route::get('/', [CajaFacturaController::class, 'getFacturas']);
        Route::post('{id}/anular', [CajaFacturaController::class, 'anularFactura']);
    });

    Route::prefix('pagos/qr')->group(function () {
        Route::post('generar', [QrPagoController::class, 'generar']);
        Route::get('estado/{codigo}', [QrPagoController::class, 'estado']);
        Route::post('webhook', [QrPagoController::class, 'webhook']);
        Route::post('simular/{codigo}', [QrPagoController::class, 'simular']);
    });

    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index']);
        Route::post('/', [ClienteController::class, 'store']);
        Route::get('{id}', [ClienteController::class, 'show']);
        Route::put('{id}', [ClienteController::class, 'update']);
        Route::delete('{id}', [ClienteController::class, 'destroy']);
        Route::post('{id}/abono', [ClienteController::class, 'registrarAbono']);
        Route::post('{id}/anticipos', [ClienteController::class, 'registrarAnticipo']);
        Route::get('{id}/anticipos-disponibles', [ClienteController::class, 'anticiposDisponibles']);
    });

    // ==========================================

    // ==========================================
    // BUCLE 9: OPERACIONES DE MESA Y SALON (frmCambiarMesa, JuntandoMesa, frmSinMesa, frmSeleccionarMesero)
    // ==========================================
    Route::prefix('mesas/{mesaId}')->group(function () {
        Route::post('cambiar-mesa', [MesaOperacionesController::class, 'cambiarMesa']);
        Route::post('juntar-mesa', [MesaOperacionesController::class, 'juntarMesa']);
        Route::post('reasignar-mesero', [MesaOperacionesController::class, 'reasignarMesero']);
    });

    Route::prefix('pedidos-sin-mesa')->group(function () {
        Route::get('/', [MesaOperacionesController::class, 'getPedidosSinMesa']);
        Route::post('/', [MesaOperacionesController::class, 'crearPedidoSinMesa']);
        Route::post('{visitaId}/asignar-mesa', [MesaOperacionesController::class, 'asignarMesaAPedidoSinMesa']);
    });

    // BUCLE 8: SEPARAR CUENTAS, SPLIT BILL, PAGOS PARCIALES Y PROPINAS (frmSepararCuentas, frmPagoParcial, FrmPropinas)
    // ==========================================
    Route::prefix('mesas/{mesaId}/separar-cuentas')->group(function () {
        Route::get('/', [SepararCuentasController::class, 'getSubcuentas']);
        Route::post('crear', [SepararCuentasController::class, 'crearSubcuenta']);
        Route::post('mover-item', [SepararCuentasController::class, 'moverItemSubcuenta']);
        Route::post('dividir-iguales', [SepararCuentasController::class, 'dividirEnPartesIguales']);
        Route::post('juntar', [SepararCuentasController::class, 'juntarCuentas']);
        Route::post('pago-parcial', [SepararCuentasController::class, 'registrarPagoParcial']);
    });
    Route::post('subcuentas/{id}/cobrar', [SepararCuentasController::class, 'cobrarSubcuenta']);
    Route::get('caja/reporte-propinas/{turnoId}', [SepararCuentasController::class, 'getReportePropinasTurno']);

    // ==========================================
    // BUCLE 6: ALMACENES, INVENTARIO, COMPRAS & RECETAS
    // ==========================================
    Route::prefix('almacenes')->group(function () {
        Route::get('/', [InventarioController::class, 'getAlmacenes']);
        Route::post('/', [InventarioController::class, 'storeAlmacen']);
        Route::put('{id}', [InventarioController::class, 'updateAlmacen']);
        Route::delete('{id}', [InventarioController::class, 'deleteAlmacen']);
    });

    Route::prefix('insumos')->group(function () {
        Route::get('/', [InventarioController::class, 'getInsumos']);
        Route::post('/', [InventarioController::class, 'storeInsumo']);
        Route::put('{id}', [InventarioController::class, 'updateInsumo']);
        Route::delete('{id}', [InventarioController::class, 'deleteInsumo']);
        Route::post('{id}/ajuste', [InventarioController::class, 'ajustarStock']);
    });

    Route::get('kardex', [InventarioController::class, 'getKardex']);

    Route::prefix('compras')->group(function () {
        Route::get('/', [CompraController::class, 'index']);
        Route::post('/', [CompraController::class, 'store']);
    });

    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index']);
        Route::post('/', [ProveedorController::class, 'store']);
        Route::put('{id}', [ProveedorController::class, 'update']);
        Route::delete('{id}', [ProveedorController::class, 'delete']);
        Route::post('{id}/pago', [ProveedorController::class, 'registrarPago']);
    });

    Route::prefix('recetas')->group(function () {
        Route::get('producto/{productoId}', [RecetaController::class, 'getReceta']);
        Route::post('producto/{productoId}', [RecetaController::class, 'guardarReceta']);
    });
    // ==========================================
    // BUCLE 7: MONITOR DE COCINA Y BARRA (KDS)
    // ==========================================
    Route::prefix('kds')->group(function () {
        Route::get('tickets', [KdsController::class, 'getTickets']);
        Route::patch('items/{id}/estado', [KdsController::class, 'cambiarEstadoItem']);
        Route::post('tickets/{visitaId}/despachar', [KdsController::class, 'despacharTicket']);
        Route::get('historial', [KdsController::class, 'getHistorial']);
        Route::post('tickets/{visitaId}/revertir', [KdsController::class, 'revertirDespacho']);
    });
    // ==========================================
    // BUCLE 11: REPORTES GERENCIALES & ANALÍTICA
    // ==========================================
    Route::prefix('reportes')->group(function () {
        Route::get('resumen-ventas', [ReporteController::class, 'getResumenVentas']);
        Route::get('top-productos', [ReporteController::class, 'getTopProductos']);
        Route::get('rendimiento-meseros', [ReporteController::class, 'getRendimientoMeseros']);
        Route::get('balance-financiero', [ReporteController::class, 'getBalanceFinanciero']);
    });
});