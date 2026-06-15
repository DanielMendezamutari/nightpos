<?php

use App\Http\Controllers\Api\V1\Admin\AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\AdminCashSessionController;
use App\Http\Controllers\Api\V1\Admin\AdminBranchController;
use App\Http\Controllers\Api\V1\Admin\AdminTenantController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\PlatformDashboardController;
use App\Http\Controllers\Api\V1\Admin\PlatformPlanController;
use App\Http\Controllers\Api\V1\Admin\PlatformSetupController;
use App\Http\Controllers\Api\V1\CashController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SettlementController;
use App\Http\Controllers\Api\V1\BraceletController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\StaffController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomServiceController;
use App\Http\Controllers\Api\V1\ShowController;
use App\Http\Controllers\Api\V1\CashMovementReasonController;
use App\Http\Controllers\Api\V1\FirstNightChecklistController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\RoomTypeController;
use App\Http\Controllers\Api\V1\ServiceAreaController;
use App\Http\Controllers\Api\V1\ShowTypeController;
use App\Http\Controllers\Api\V1\WaiterController;
use App\Http\Controllers\Api\V1\CleaningController;
use App\Http\Controllers\Api\V1\GirlController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\SettingsBootstrapController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\ShiftConsoleController;
use App\Http\Controllers\Api\V1\ShiftExportController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\DirectSaleController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\EventsTokenController;
use App\Http\Controllers\Api\V1\EventsStreamController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login-pin', [AuthController::class, 'loginPin']);
        Route::post('login-password', [AuthController::class, 'loginPassword']);
        Route::get('login-context/tenants', [AuthController::class, 'loginContextTenants']);
        Route::get('login-context/branches', [AuthController::class, 'loginContextBranches']);

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware(['auth:api', 'nightpos.tenant', 'nightpos.branch:optional'])->group(function () {
        Route::get('tenant/current', [TenantController::class, 'current']);

        Route::get('branches/available', [BranchController::class, 'available']);

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access'])->group(function () {
            Route::get('branches/current', [BranchController::class, 'current']);
        });

        Route::prefix('admin')->group(function () {
            Route::middleware('nightpos.permission:admin.tenants.list')->group(function () {
                Route::get('tenants', [AdminTenantController::class, 'index']);
                Route::get('tenants/{id}', [AdminTenantController::class, 'show'])->whereNumber('id');
                Route::put('tenants/{id}', [AdminTenantController::class, 'update'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.tenants.create')->group(function () {
                Route::post('tenants', [AdminTenantController::class, 'store']);
            });

            Route::middleware('nightpos.permission:platform.setup')->group(function () {
                Route::post('platform/setup', [PlatformSetupController::class, 'store']);
            });

            Route::middleware('nightpos.permission:admin.tenants.list')->prefix('platform')->group(function () {
                Route::get('dashboard', [PlatformDashboardController::class, 'index']);
                Route::get('plans', [PlatformPlanController::class, 'index']);
                Route::get('plans/{id}/limits', [PlatformPlanController::class, 'limits'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.tenants.create')->prefix('platform')->group(function () {
                Route::post('plans', [PlatformPlanController::class, 'store']);
                Route::post('plans/{id}/duplicate', [PlatformPlanController::class, 'duplicate'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.tenants.update')->prefix('platform')->group(function () {
                Route::put('plans/{id}', [PlatformPlanController::class, 'update'])->whereNumber('id');
                Route::delete('plans/{id}', [PlatformPlanController::class, 'destroy'])->whereNumber('id');
                Route::put('plans/{id}/limits', [PlatformPlanController::class, 'updateLimits'])->whereNumber('id');
            });


            Route::middleware('nightpos.permission:admin.branches.list')->group(function () {
                Route::get('branches', [AdminBranchController::class, 'index']);
                Route::get('branches/{id}', [AdminBranchController::class, 'show'])->whereNumber('id');
                Route::put('branches/{id}', [AdminBranchController::class, 'update'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.branches.create')->group(function () {
                Route::post('branches', [AdminBranchController::class, 'store']);
            });

            Route::middleware('nightpos.permission:admin.users.list')->group(function () {
                Route::get('users', [AdminUserController::class, 'index']);
                Route::get('users/{id}', [AdminUserController::class, 'show'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.users.create')->group(function () {
                Route::post('users', [AdminUserController::class, 'store']);
                Route::post('users/{id}/reset-pin', [AdminUserController::class, 'resetPin'])->whereNumber('id');
                Route::post('users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->whereNumber('id');
                Route::post('users/{id}/branches', [AdminUserController::class, 'grantBranch'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:admin.users.update')->group(function () {
                Route::put('users/{id}', [AdminUserController::class, 'update'])->whereNumber('id');
                Route::delete('users/{id}/branches/{branchId}', [AdminUserController::class, 'revokeBranch'])
                    ->whereNumber(['id', 'branchId']);
            });

            Route::middleware('nightpos.permission:roles.access')->group(function () {
                Route::get('roles', [AdminRoleController::class, 'index']);
                Route::get('roles/{id}', [AdminRoleController::class, 'show'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:permissions.access')->group(function () {
                Route::get('permissions', [AdminRoleController::class, 'permissions']);
            });

            Route::middleware('nightpos.permission:roles.create')->group(function () {
                Route::post('roles', [AdminRoleController::class, 'store']);
            });

            Route::middleware('nightpos.permission:roles.update')->group(function () {
                Route::put('roles/{id}', [AdminRoleController::class, 'update'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:roles.permissions.update')->group(function () {
                Route::put('roles/{id}/permissions', [AdminRoleController::class, 'updatePermissions'])->whereNumber('id');
            });

            Route::middleware('nightpos.permission:roles.delete')->group(function () {
                Route::delete('roles/{id}', [AdminRoleController::class, 'destroy'])->whereNumber('id');
            });

            Route::middleware(['nightpos.branch:required', 'nightpos.branch.access'])->group(function () {
                Route::middleware('nightpos.permission:admin.cash_sessions.summary')->group(function () {
                    Route::get('cash-sessions/summary', [AdminCashSessionController::class, 'summary']);
                });

                Route::middleware('nightpos.permission:admin.cash_sessions.list')->group(function () {
                    Route::get('cash-sessions', [AdminCashSessionController::class, 'index']);
                });

                Route::middleware('nightpos.permission:admin.cash_sessions.view')->group(function () {
                    Route::get('cash-sessions/{id}', [AdminCashSessionController::class, 'show'])->whereNumber('id');
                });
            });
        });

        Route::middleware('nightpos.permission:products.list')->group(function () {
            Route::get('products/pos-catalog', [ProductController::class, 'posCatalog']);
            Route::get('products', [ProductController::class, 'index']);
            Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');
            Route::get('products/{id}/prices', [ProductController::class, 'prices'])->whereNumber('id');
            Route::get('product-categories', [ProductCategoryController::class, 'index']);
            Route::get('product-categories/{id}', [ProductCategoryController::class, 'show'])->whereNumber('id');
        });

        Route::middleware('nightpos.permission:products.create')->group(function () {
            Route::post('products', [ProductController::class, 'store']);
            Route::post('products/{id}/prices', [ProductController::class, 'storePrice'])->whereNumber('id');
            Route::post('product-categories', [ProductCategoryController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:products.quick_create'])->group(function () {
            Route::post('products/quick', [ProductController::class, 'quickStore']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:product_prices.quick_create'])->group(function () {
            Route::post('products/{id}/quick-prices', [ProductController::class, 'storePrice'])->whereNumber('id');
        });

        Route::middleware('nightpos.permission:products.update')->group(function () {
            Route::put('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
            Route::put('products/{id}/prices/active', [ProductController::class, 'replaceActivePrice'])->whereNumber('id');
            Route::put('product-categories/{id}', [ProductCategoryController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shift_console.access'])->group(function () {
            Route::get('shift-console/current', [ShiftConsoleController::class, 'current']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:reports.access'])->group(function () {
            Route::prefix('reports')->group(function () {
                Route::get('daily', [ReportController::class, 'daily']);
                Route::get('sales', [ReportController::class, 'sales']);
                Route::get('cash', [ReportController::class, 'cash']);
                Route::get('services', [ReportController::class, 'services']);
                Route::get('settlements', [ReportController::class, 'settlements']);
                Route::get('rooms', [ReportController::class, 'rooms']);
                Route::get('shift-closure', [ReportController::class, 'shiftClosure']);
                Route::get('product-reconciliation', [ReportController::class, 'productReconciliation']);
            });
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shifts.access'])->group(function () {
            Route::get('shifts/current', [ShiftController::class, 'current']);
            Route::get('shifts', [ShiftController::class, 'index'])->middleware('nightpos.permission:shifts.list');
            Route::get('shifts/{id}', [ShiftController::class, 'show'])->whereNumber('id')->middleware('nightpos.permission:shifts.list');
            Route::get('shifts/{id}/summary', [ShiftController::class, 'summary'])->whereNumber('id')->middleware('nightpos.permission:shifts.list');
            Route::get('shifts/{id}/export.csv', [ShiftExportController::class, 'csv'])->whereNumber('id')->middleware('nightpos.permission:shifts.list');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shifts.open'])->group(function () {
            Route::post('shifts/open', [ShiftController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shifts.close'])->group(function () {
            Route::post('shifts/{id}/close', [ShiftController::class, 'close'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:waiter.dashboard'])->group(function () {
            Route::get('waiter/dashboard', [WaiterController::class, 'dashboard']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:waiter.orders'])->group(function () {
            Route::get('waiter/orders', [WaiterController::class, 'orders']);
            Route::get('waiter/orders/active', [WaiterController::class, 'activeOrders']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cleaning.dashboard'])->group(function () {
            Route::get('cleaning/dashboard', [CleaningController::class, 'dashboard']);
            Route::get('cleaning/shift-earnings', [CleaningController::class, 'shiftEarnings']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cleaning.room_services'])->group(function () {
            Route::get('cleaning/rooms', [CleaningController::class, 'rooms']);
            Route::get('cleaning/room-services/active', [CleaningController::class, 'activeServices']);
            Route::get('cleaning/room-services/due', [CleaningController::class, 'dueServices']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cleaning.check'])->group(function () {
            Route::post('cleaning/room-services/{id}/check', [CleaningController::class, 'check'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cleaning.finish'])->group(function () {
            Route::post('cleaning/room-services/{id}/finish', [CleaningController::class, 'finish'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cleaning.mark_clean'])->group(function () {
            Route::post('cleaning/rooms/{id}/mark-clean', [CleaningController::class, 'markClean'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:girl.earnings.view'])->group(function () {
            Route::get('girl/shift-earnings', [GirlController::class, 'shiftEarnings']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.access'])->group(function () {
            Route::get('orders', [OrderController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.create'])->group(function () {
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('waiter/service-areas', [WaiterController::class, 'serviceAreas']);
            Route::get('waiter/girls', [WaiterController::class, 'girls']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.access'])->group(function () {
            Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.add_items'])->group(function () {
            Route::post('orders/{id}/items', [OrderController::class, 'addItem'])->whereNumber('id');
            Route::patch('orders/{id}/items/{itemId}', [OrderController::class, 'assignItemGirl'])->whereNumber(['id', 'itemId']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.update_items'])->group(function () {
            Route::put('orders/{id}/items/{itemId}', [OrderController::class, 'updateItem'])->whereNumber(['id', 'itemId']);
            Route::delete('orders/{id}/items/{itemId}', [OrderController::class, 'removeItem'])->whereNumber(['id', 'itemId']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.cancel_item'])->group(function () {
            Route::post('orders/{id}/items/{itemId}/cancel', [OrderController::class, 'cancelItem'])->whereNumber(['id', 'itemId']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.update_header'])->group(function () {
            Route::patch('orders/{id}', [OrderController::class, 'updateHeader'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.send_to_bar'])->group(function () {
            Route::post('orders/{id}/send-to-bar', [OrderController::class, 'sendToBar'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:orders.cancel'])->group(function () {
            Route::post('orders/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:sales.charge'])->group(function () {
            Route::post('orders/{id}/charge', [OrderController::class, 'charge'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:sales.list'])->group(function () {
            Route::get('sales', [SaleController::class, 'index']);
            Route::get('sales/{id}', [SaleController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:sales.direct_create'])->group(function () {
            Route::post('direct-sales', [DirectSaleController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:cash.access'])->group(function () {
            Route::get('cash/session/current', [CashController::class, 'current']);
            Route::post('cash/session/open', [CashController::class, 'open']);
            Route::post('cash/movements', [CashController::class, 'registerMovement']);
            Route::post('cash/session/close', [CashController::class, 'close']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.access'])->group(function () {
            Route::get('settlements/current-shift', [SettlementController::class, 'currentShift']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.pending_sources'])->group(function () {
            Route::get('settlements/current-shift/pending-sources', [SettlementController::class, 'pendingSources']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.history'])->group(function () {
            Route::get('settlements/history', [SettlementController::class, 'history']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.generate'])->group(function () {
            Route::post('settlements/generate-current-shift', [SettlementController::class, 'generateCurrentShift']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.access'])->group(function () {
            Route::get('settlements/{id}', [SettlementController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settlements.pay'])->group(function () {
            Route::post('settlements/{id}/mark-paid', [SettlementController::class, 'markPaid'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:staff.quick_create_girl'])->group(function () {
            Route::get('staff/girls', [StaffController::class, 'girls']);
            Route::post('staff/quick-girls', [StaffController::class, 'quickCreateGirl']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:staff.quick_create_waiter'])->group(function () {
            Route::get('staff/waiters', [StaffController::class, 'waiters']);
            Route::post('staff/quick-waiters', [StaffController::class, 'quickCreateWaiter']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:show_types.access'])->group(function () {
            Route::get('show-types', [ShowTypeController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:show_types.create'])->group(function () {
            Route::post('show-types', [ShowTypeController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:show_types.update'])->group(function () {
            Route::put('show-types/{id}', [ShowTypeController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:bracelets.access'])->group(function () {
            Route::get('bracelets', [BraceletController::class, 'index']);
            Route::get('bracelets/{id}', [BraceletController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:bracelets.create'])->group(function () {
            Route::post('bracelets', [BraceletController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:room_services.cleaning_view'])->group(function () {
            Route::get('room-services/active', [RoomServiceController::class, 'active']);
            Route::get('room-services/due', [RoomServiceController::class, 'due']);
            Route::get('room-services/control', [RoomServiceController::class, 'control']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:room_services.access'])->group(function () {
            Route::get('room-services', [RoomServiceController::class, 'index']);
            Route::get('room-services/{id}', [RoomServiceController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:room_services.create'])->group(function () {
            Route::post('room-services', [RoomServiceController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:room_services.finish'])->group(function () {
            Route::post('room-services/{id}/finish', [RoomServiceController::class, 'finish'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:room_services.check'])->group(function () {
            Route::post('room-services/{id}/check', [RoomServiceController::class, 'check'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:rooms.access'])->group(function () {
            Route::get('rooms', [RoomController::class, 'index']);
            Route::get('rooms/available', [RoomController::class, 'available']);
            Route::get('rooms/cleaning', [RoomController::class, 'cleaning']);
            Route::get('rooms/{id}', [RoomController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:rooms.create'])->group(function () {
            Route::post('rooms', [RoomController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:rooms.update'])->group(function () {
            Route::put('rooms/{id}', [RoomController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:rooms.clean'])->group(function () {
            Route::post('rooms/{id}/mark-clean', [RoomController::class, 'markClean'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:rooms.maintenance'])->group(function () {
            Route::post('rooms/{id}/mark-maintenance', [RoomController::class, 'markMaintenance'])->whereNumber('id');
            Route::post('rooms/{id}/mark-available', [RoomController::class, 'markAvailable'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:notifications.access'])->group(function () {
            Route::get('notifications', [NotificationController::class, 'index']);
            Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
            Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shows.access'])->group(function () {
            Route::get('shows', [ShowController::class, 'index']);
            Route::get('shows/{id}', [ShowController::class, 'show'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:shows.create'])->group(function () {
            Route::post('shows', [ShowController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.cash_reasons'])->group(function () {
            Route::get('cash-movement-reasons', [CashMovementReasonController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.cash_reasons.manage'])->group(function () {
            Route::post('cash-movement-reasons', [CashMovementReasonController::class, 'store']);
            Route::put('cash-movement-reasons/{id}', [CashMovementReasonController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.payment_methods'])->group(function () {
            Route::get('payment-methods', [PaymentMethodController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.payment_methods.manage'])->group(function () {
            Route::post('payment-methods', [PaymentMethodController::class, 'store']);
            Route::put('payment-methods/{id}', [PaymentMethodController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.service_areas'])->group(function () {
            Route::get('service-areas', [ServiceAreaController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.service_areas.manage'])->group(function () {
            Route::post('service-areas', [ServiceAreaController::class, 'store']);
            Route::put('service-areas/{id}', [ServiceAreaController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.room_types'])->group(function () {
            Route::get('room-types', [RoomTypeController::class, 'index']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.room_types.manage'])->group(function () {
            Route::post('room-types', [RoomTypeController::class, 'store']);
            Route::put('room-types/{id}', [RoomTypeController::class, 'update'])->whereNumber('id');
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.checklist'])->group(function () {
            Route::get('settings/first-night-checklist', [FirstNightChecklistController::class, 'show']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:settings.bootstrap'])->group(function () {
            Route::post('settings/bootstrap-operational', [SettingsBootstrapController::class, 'store']);
        });

        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access', 'nightpos.permission:audits.list'])->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index']);
        });

        // SSE — token endpoint (requires branch context)
        Route::middleware(['nightpos.branch:required', 'nightpos.branch.access'])->group(function () {
            Route::post('events/token', EventsTokenController::class)->name('events.token');
        });
    });
});

// SSE stream — no JWT auth, uses short-lived token in query string
Route::prefix('v1')->group(function () {
    Route::get('events/stream', EventsStreamController::class)->name('events.stream');
});
