<?php

use App\Http\Controllers\Api\AddOrderItemController;
use App\Http\Controllers\Api\AddPosOrderItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssignBranchTableController;
use App\Http\Controllers\Api\AckRoomServiceAlertController;
use App\Http\Controllers\Api\ApplyRefillRecipeController;
use App\Http\Controllers\Api\CloseShiftController;
use App\Http\Controllers\Api\ClosePosSessionController;
use App\Http\Controllers\Api\CloseRoomTimeServiceController;
use App\Http\Controllers\Api\GetCurrentOpenShiftController;
use App\Http\Controllers\Api\GetPosOrderController;
use App\Http\Controllers\Api\GetShiftCashierReportController;
use App\Http\Controllers\Api\GetShiftCashSummaryController;
use App\Http\Controllers\Api\ListCompanionWorkSessionsController;
use App\Http\Controllers\Api\ListRecentShiftsForSiteController;
use App\Http\Controllers\Api\SettleCompanionWorkSessionController;
use App\Http\Controllers\Api\StartCompanionWorkSessionController;
use App\Http\Controllers\Api\ListCashDrawerMovementsController;
use App\Http\Controllers\Api\RegisterCashDrawerMovementController;
use App\Http\Controllers\Api\CompanionRankingReportController;
use App\Http\Controllers\Api\CreateBranchController;
use App\Http\Controllers\Api\CreateBranchContactController;
use App\Http\Controllers\Api\CreateCompanionQuickController;
use App\Http\Controllers\Api\CreatePosOrderController;
use App\Http\Controllers\Api\CreatePosSessionController;
use App\Http\Controllers\Api\CreatePurchaseOrderController;
use App\Http\Controllers\Api\CreateRoomTimeServiceController;
use App\Http\Controllers\Api\CreateSiteStockTransferController;
use App\Http\Controllers\Api\GetSiteStockTransferController;
use App\Http\Controllers\Api\ListSiteStockTransfersController;
use App\Http\Controllers\Api\CreateRefillRecipeController;
use App\Http\Controllers\Api\CreateProductCategoryController;
use App\Http\Controllers\Api\CreateProductController;
use App\Http\Controllers\Api\CreateUserController;
use App\Http\Controllers\Api\DeleteProductCategoryController;
use App\Http\Controllers\Api\ListProductCategoriesController;
use App\Http\Controllers\Api\ListMaintenanceProductsController;
use App\Http\Controllers\Api\ListCompanionsController;
use App\Http\Controllers\Api\ListPosOrdersController;
use App\Http\Controllers\Api\ListPosSessionsController;
use App\Http\Controllers\Api\ListWaiterTablesController;
use App\Http\Controllers\Api\ListProductKardexController;
use App\Http\Controllers\Api\ListProductsController;
use App\Http\Controllers\Api\CancelPurchaseOrderController;
use App\Http\Controllers\Api\DownloadPurchaseOrderDocumentController;
use App\Http\Controllers\Api\ExportPurchaseOrderPdfController;
use App\Http\Controllers\Api\UploadPurchaseOrderDocumentController;
use App\Http\Controllers\Api\ListPurchaseOrdersController;
use App\Http\Controllers\Api\ShowPurchaseOrderController;
use App\Http\Controllers\Api\ListRefillRecipesController;
use App\Http\Controllers\Api\ListRoomServiceAlertsController;
use App\Http\Controllers\Api\ListRoomTimeServicesController;
use App\Http\Controllers\Api\ListBranchWaitersController;
use App\Http\Controllers\Api\ListUsersController;
use App\Http\Controllers\Api\UpdateBranchWaiterCompensationController;
use App\Http\Controllers\Api\ListWaiterCommissionsController;
use App\Http\Controllers\Api\ListValuedKardexController;
use App\Http\Controllers\Api\ExportStockTransferPdfController;
use App\Http\Controllers\Api\ExportProductKardexPdfController;
use App\Http\Controllers\Api\ExportValuedKardexPdfController;
use App\Http\Controllers\Api\ExportShiftCashPdfController;
use App\Http\Controllers\Api\ExportPosOrderPdfController;
use App\Http\Controllers\Api\ExportRoomTimeServicePdfController;
use App\Http\Controllers\Api\ExportCompanionRankingPdfController;
use App\Http\Controllers\Api\ExportProductSalesPdfController;
use App\Http\Controllers\Api\ExportSalesSummaryPdfController;
use App\Http\Controllers\Api\ExportStaffSalesPdfController;
use App\Http\Controllers\Api\ExportWaiterCommissionsPdfController;
use App\Http\Controllers\Api\ListProductSalesReportController;
use App\Http\Controllers\Api\ListReportShiftTurnsController;
use App\Http\Controllers\Api\ListSalesSummaryReportController;
use App\Http\Controllers\Api\ListStaffSalesReportController;
use App\Http\Controllers\Api\ExportSaasSubscriptionPaymentsPdfController;
use App\Http\Controllers\Api\ExportProductsCatalogPdfController;
use App\Http\Controllers\Api\ExportBranchProfilePdfController;
use App\Http\Controllers\Api\ExportRefillRecipesPdfController;
use App\Http\Controllers\Api\OpenShiftController;
use App\Http\Controllers\Api\RegisterPaymentController;
use App\Http\Controllers\Api\RegisterManualInventoryMovementController;
use App\Http\Controllers\Api\PayRoomTimeServiceController;
use App\Http\Controllers\Api\SaasAlertsController;
use App\Http\Controllers\Api\SaasOverviewController;
use App\Http\Controllers\Api\SaasSubscriptionController;
use App\Http\Controllers\Api\UnassignBranchTableController;
use App\Http\Controllers\Api\UpdateSystemLockController;
use App\Http\Controllers\Api\UpdateProductCategoryController;
use App\Http\Controllers\Api\UpdateProductController;
use App\Http\Controllers\Api\UpdateWaiterTableLimitController;
use App\Http\Controllers\Api\ListSitesController;
use App\Http\Controllers\Api\BranchProfileController;
use App\Http\Controllers\Api\BranchOperatingHoursController;
use App\Http\Controllers\Api\CreateBranchRoomController;
use App\Http\Controllers\Api\CreateBranchTableController;
use App\Http\Controllers\Api\DeleteBranchRoomController;
use App\Http\Controllers\Api\DeleteBranchTableController;
use App\Http\Controllers\Api\DeleteBranchContactController;
use App\Http\Controllers\Api\DeleteUserController;
use App\Http\Controllers\Api\ListBranchRoomsController;
use App\Http\Controllers\Api\ListBranchTablesController;
use App\Http\Controllers\Api\ListBranchContactsController;
use App\Http\Controllers\Api\UpdateBranchRoomController;
use App\Http\Controllers\Api\UpdateBranchContactController;
use App\Http\Controllers\Api\UpdateUserController;
use App\Http\Controllers\Api\ExtendRoomTimeServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'nightpos-api',
    ]);
});

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/me', [AuthController::class, 'updateMe']);
    Route::get('/auth/site-options', [AuthController::class, 'siteOptions']);
    Route::patch('/auth/active-site', [AuthController::class, 'setActiveSite']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api', 'system.active'])->group(function () {
    Route::middleware(['role:cashier|admin|super_admin', 'admin.site.scope'])->group(function () {
        Route::get('/shifts/current', GetCurrentOpenShiftController::class);
        Route::post('/shifts/open', OpenShiftController::class);
    });

    Route::middleware(['role:cashier|manager|admin|super_admin', 'admin.site.scope'])->group(function () {
        Route::get('/shifts/history', ListRecentShiftsForSiteController::class);
        Route::get('/shifts/{shiftTurnId}/cashier-report', GetShiftCashierReportController::class);
        Route::get('/shifts/{shiftTurnId}/companion-work-sessions', ListCompanionWorkSessionsController::class);
        Route::get('/branch/waiters', ListBranchWaitersController::class);
        Route::patch('/branch/waiters/{userId}/compensation', UpdateBranchWaiterCompensationController::class);
    });

    Route::middleware(['role:cashier|admin|super_admin', 'admin.site.scope', 'cashier.open.shift'])->group(function () {
        Route::get('/shifts/{shiftTurnId}/cash-summary', GetShiftCashSummaryController::class);
        Route::get('/shifts/{shiftTurnId}/pdf', ExportShiftCashPdfController::class);
        Route::get('/shifts/{shiftTurnId}/cash-movements', ListCashDrawerMovementsController::class);
        Route::post('/shifts/{shiftTurnId}/cash-movements', RegisterCashDrawerMovementController::class);
        Route::post('/shifts/{shiftTurnId}/close', CloseShiftController::class);
        Route::post('/shifts/{shiftTurnId}/companion-work-sessions', StartCompanionWorkSessionController::class);
        Route::post('/companion-work-sessions/{sessionId}/settle', SettleCompanionWorkSessionController::class);
        Route::post('/payments', RegisterPaymentController::class);
        Route::post('/room-services', CreateRoomTimeServiceController::class);
        Route::get('/room-services', ListRoomTimeServicesController::class);
        Route::get('/room-services/alerts', ListRoomServiceAlertsController::class);
        Route::get('/room-services/{serviceId}/pdf', ExportRoomTimeServicePdfController::class);
        Route::post('/room-services/{serviceId}/extend', ExtendRoomTimeServiceController::class);
        Route::post('/room-services/{serviceId}/close', CloseRoomTimeServiceController::class);
        Route::post('/room-services/{serviceId}/pay', PayRoomTimeServiceController::class);
        Route::post('/room-services/{serviceId}/alerts/ack', AckRoomServiceAlertController::class);
    });

    /*
     * Lectura de órdenes POS: mesero + cajero/admin. Registrar una sola vez: si se declara dos veces,
     * Laravel deja la última ruta y el mesero recibe 403 en GET /pos/orders.
     */
    Route::middleware(['role:waiter|cashier|admin|super_admin', 'cashier.open.shift'])->group(function () {
        Route::get('/pos/orders', ListPosOrdersController::class);
        Route::get('/pos/orders/{orderId}', GetPosOrderController::class);
        Route::get('/pos/orders/{orderId}/pdf', ExportPosOrderPdfController::class);
    });

    Route::middleware(['role:waiter'])->group(function () {
        Route::get('/waiter/tables', ListWaiterTablesController::class);
        Route::post('/orders/items', AddOrderItemController::class);
        Route::get('/pos/sessions', ListPosSessionsController::class);
        Route::post('/pos/sessions', CreatePosSessionController::class);
        Route::post('/pos/sessions/{sessionId}/close', ClosePosSessionController::class);
        Route::post('/pos/orders', CreatePosOrderController::class);
        Route::post('/pos/orders/{orderId}/items', AddPosOrderItemController::class);
    });

    Route::middleware(['role:waiter|cashier|manager|admin|super_admin'])->group(function () {
        Route::get('/products', ListProductsController::class);
        Route::get('/companions', ListCompanionsController::class);
    });

    Route::middleware(['role:cashier|manager|admin|super_admin|owner'])->group(function () {
        Route::get('/reports/shift-turns', ListReportShiftTurnsController::class);
        Route::get('/reports/products/sold', ListProductSalesReportController::class);
        Route::get('/reports/products/sold/pdf', ExportProductSalesPdfController::class);
        Route::get('/reports/sales/summary', ListSalesSummaryReportController::class);
        Route::get('/reports/sales/summary/pdf', ExportSalesSummaryPdfController::class);
        Route::get('/reports/staff/sales', ListStaffSalesReportController::class);
        Route::get('/reports/staff/sales/pdf', ExportStaffSalesPdfController::class);
        Route::get('/reports/companions/ranking', CompanionRankingReportController::class);
        Route::get('/reports/companions/ranking/pdf', ExportCompanionRankingPdfController::class);
        Route::get('/reports/waiters/commissions', ListWaiterCommissionsController::class);
        Route::get('/reports/waiters/commissions/pdf', ExportWaiterCommissionsPdfController::class);
        Route::post('/companions/quick-create', CreateCompanionQuickController::class);
    });

    Route::middleware(['role:admin|super_admin|manager|owner'])->group(function () {
        Route::get('/product-categories', ListProductCategoriesController::class);
        Route::post('/product-categories', CreateProductCategoryController::class);
        Route::patch('/product-categories/{categoryId}', UpdateProductCategoryController::class);
        Route::delete('/product-categories/{categoryId}', DeleteProductCategoryController::class);
    });

    Route::middleware(['role:admin|super_admin|manager|owner'])->group(function () {
        Route::post('/products', CreateProductController::class);
        Route::patch('/products/{productId}', UpdateProductController::class);
    });

    Route::middleware(['role:owner|super_admin'])->group(function () {
        Route::get('/sites', ListSitesController::class);
    });

    Route::middleware(['role:admin|super_admin|manager|owner'])->group(function () {
        Route::get('/branch/profile/pdf', ExportBranchProfilePdfController::class);
        Route::get('/branch/profile', [BranchProfileController::class, 'show']);
        Route::patch('/branch/profile', [BranchProfileController::class, 'update']);
        Route::post('/branch/logo', [BranchProfileController::class, 'uploadLogo']);
        Route::get('/branch/operating-hours', [BranchOperatingHoursController::class, 'show']);
        Route::put('/branch/operating-hours', [BranchOperatingHoursController::class, 'sync']);
        Route::get('/branch/rooms', ListBranchRoomsController::class);
        Route::post('/branch/rooms', CreateBranchRoomController::class);
        Route::patch('/branch/rooms/{roomId}', UpdateBranchRoomController::class);
        Route::delete('/branch/rooms/{roomId}', DeleteBranchRoomController::class);
        Route::get('/branch/tables', ListBranchTablesController::class);
        Route::post('/branch/tables', CreateBranchTableController::class);
        Route::delete('/branch/tables/{tableId}', DeleteBranchTableController::class);
        Route::post('/branch/tables/{tableId}/assign', AssignBranchTableController::class);
        Route::delete('/branch/tables/{tableId}/assign', UnassignBranchTableController::class);
        Route::patch('/branch/waiters/{userId}/table-limit', UpdateWaiterTableLimitController::class);
        Route::get('/branch/contacts', ListBranchContactsController::class);
        Route::post('/branch/contacts', CreateBranchContactController::class);
        Route::patch('/branch/contacts/{contactId}', UpdateBranchContactController::class);
        Route::delete('/branch/contacts/{contactId}', DeleteBranchContactController::class);
        Route::get('/maintenance/products/pdf', ExportProductsCatalogPdfController::class);
        Route::get('/maintenance/products', ListMaintenanceProductsController::class);
        Route::post('/maintenance/movements', RegisterManualInventoryMovementController::class);
        Route::get('/maintenance/products/{productId}/kardex/pdf', ExportProductKardexPdfController::class);
        Route::get('/maintenance/products/{productId}/kardex', ListProductKardexController::class);
        Route::get('/maintenance/kardex-valued/pdf', ExportValuedKardexPdfController::class);
        Route::get('/maintenance/kardex-valued', ListValuedKardexController::class);
        Route::get('/maintenance/refill-recipes/pdf', ExportRefillRecipesPdfController::class);
        Route::get('/maintenance/refill-recipes', ListRefillRecipesController::class);
        Route::post('/maintenance/refill-recipes', CreateRefillRecipeController::class);
        Route::post('/maintenance/refill-recipes/{recipeId}/apply', ApplyRefillRecipeController::class);
        Route::get('/maintenance/purchases', ListPurchaseOrdersController::class);
        Route::post('/maintenance/purchases', CreatePurchaseOrderController::class);
        Route::get('/maintenance/purchases/{purchaseOrderId}', ShowPurchaseOrderController::class);
        Route::post('/maintenance/purchases/{purchaseOrderId}/document', UploadPurchaseOrderDocumentController::class);
        Route::get('/maintenance/purchases/{purchaseOrderId}/document', DownloadPurchaseOrderDocumentController::class);
        Route::get('/maintenance/purchases/{purchaseOrderId}/pdf', ExportPurchaseOrderPdfController::class);
        Route::post('/maintenance/purchases/{purchaseOrderId}/cancel', CancelPurchaseOrderController::class);
        Route::get('/maintenance/transfers', ListSiteStockTransfersController::class);
        Route::post('/maintenance/transfers', CreateSiteStockTransferController::class);
        Route::get('/maintenance/transfers/{transferId}/pdf', ExportStockTransferPdfController::class);
        Route::get('/maintenance/transfers/{transferId}', GetSiteStockTransferController::class);
    });

    Route::middleware(['role:owner|super_admin|admin'])->group(function () {
        Route::get('/users', ListUsersController::class);
        Route::post('/users', CreateUserController::class);
        Route::patch('/users/{userId}', UpdateUserController::class);
        Route::delete('/users/{userId}', DeleteUserController::class);
    });
});

Route::middleware(['auth:api', 'role:owner'])->group(function () {
    Route::patch('/system/lock', UpdateSystemLockController::class);
    Route::post('/branches', CreateBranchController::class);
    Route::get('/saas/overview', SaasOverviewController::class);
    Route::get('/saas/alerts', SaasAlertsController::class);
    Route::get('/saas/quote', [SaasSubscriptionController::class, 'quote']);
    Route::get('/saas/subscriptions', [SaasSubscriptionController::class, 'index']);
    Route::get('/saas/subscriptions/{siteId}/payments', [SaasSubscriptionController::class, 'paymentHistory']);
    Route::get('/saas/subscriptions/{siteId}/payments/pdf', ExportSaasSubscriptionPaymentsPdfController::class);
    Route::get('/saas/subscriptions/{siteId}/payments/export', [SaasSubscriptionController::class, 'exportPaymentsCsv']);
    Route::post('/saas/subscriptions/{siteId}/payments', [SaasSubscriptionController::class, 'registerPayment']);
    Route::patch('/saas/subscriptions/{siteId}/status', [SaasSubscriptionController::class, 'updateStatus']);
    Route::patch('/saas/subscriptions/{siteId}/monthly-fee', [SaasSubscriptionController::class, 'updateMonthlyFee']);
});
