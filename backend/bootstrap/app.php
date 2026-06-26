<?php

use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Auth\Exceptions\TenantAccessDeniedException;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Infrastructure\Laravel\Http\ApiJwtExceptionRenderer;
use App\Infrastructure\Laravel\Http\Middleware\AuthenticatePrintDeviceMiddleware;
use App\Infrastructure\Laravel\Http\Middleware\EnsureRolePermissionMiddleware;
use App\Infrastructure\Laravel\Http\Middleware\EnsureUserHasBranchAccessMiddleware;
use App\Infrastructure\Laravel\Http\Middleware\ResolveBranchMiddleware;
use App\Infrastructure\Laravel\Http\Middleware\ResolveTenantMiddleware;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Sale\Exceptions\SaleDomainException;
use App\Domain\Sale\Exceptions\SaleNotFoundException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\User\Exceptions\UserDomainException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\Branch\Exceptions\BranchDomainException;
use App\Domain\Branch\Exceptions\BranchNotFoundException;
use App\Domain\Product\Exceptions\ProductCategoryNotFoundException;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Role\Exceptions\RoleAdminException;
use App\Domain\Shift\Exceptions\OfficialShiftNotFoundException;
use App\Domain\GirlIncome\Exceptions\BraceletNotFoundException;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\ShowType\Exceptions\ShowTypeDomainException;
use App\Domain\Room\Exceptions\RoomDomainException;
use App\Domain\Room\Exceptions\RoomNotFoundException;
use App\Domain\GirlIncome\Exceptions\RoomServiceNotFoundException;
use App\Domain\GirlIncome\Exceptions\ShowNotFoundException;
use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffFineNotFoundException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\Shift\Exceptions\ShiftDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Exceptions\SettlementCashSessionRequiredException;
use App\Domain\Plan\Exceptions\PlanDomainException;
use App\Domain\Plan\Exceptions\PlanNotFoundException;
use App\Domain\Tenant\Exceptions\TenantDomainException;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Shared\Domain\Exceptions\DomainException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'nightpos.tenant' => ResolveTenantMiddleware::class,
            'nightpos.branch' => ResolveBranchMiddleware::class,
            'nightpos.branch.access' => EnsureUserHasBranchAccessMiddleware::class,
            'nightpos.permission' => EnsureRolePermissionMiddleware::class,
            'nightpos.print-device' => AuthenticatePrintDeviceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        ApiJwtExceptionRenderer::register(
            static fn (callable $handler) => $exceptions->render($handler),
        );

        $exceptions->render(function (DomainException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof InvalidCredentialsException => 401,
                $exception instanceof TenantAccessDeniedException,
                $exception instanceof BranchAccessDeniedException,
                $exception instanceof PermissionDeniedException => 403,
                $exception instanceof ProductNotFoundException,
                $exception instanceof OrderNotFoundException,
                $exception instanceof CashSessionNotFoundException,
                $exception instanceof SaleNotFoundException,
                $exception instanceof UserNotFoundException,
                $exception instanceof TenantNotFoundException,
                $exception instanceof PlanNotFoundException,
                $exception instanceof BranchNotFoundException,
                $exception instanceof ProductCategoryNotFoundException,
                $exception instanceof OfficialShiftNotFoundException,
                $exception instanceof StaffSettlementNotFoundException,
                $exception instanceof StaffFineNotFoundException,
                $exception instanceof BraceletNotFoundException,
                $exception instanceof RoomServiceNotFoundException,
                $exception instanceof ShowNotFoundException,
                $exception instanceof RoomNotFoundException => 404,
                $exception instanceof RoleAdminException => $exception->statusCode,
                $exception instanceof PrintingDomainException => $exception->statusCode,
                $exception instanceof ProductDomainException,
                $exception instanceof TenantDomainException,
                $exception instanceof PlanDomainException,
                $exception instanceof BranchDomainException,
                $exception instanceof ShiftDomainException,
                $exception instanceof UserDomainException,
                $exception instanceof CashDomainException,
                $exception instanceof SaleDomainException,
                $exception instanceof GirlIncomeDomainException,
                $exception instanceof RoomDomainException,
                $exception instanceof ShowTypeDomainException,
                $exception instanceof StaffSettlementDomainException,
                $exception instanceof StaffFineDomainException,
                $exception instanceof SettlementCashSessionRequiredException => 422,
                default => 422,
            };

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => $exception instanceof SettlementCashSessionRequiredException && config('app.debug')
                    ? (object) ['cash_session_debug' => $exception->debugContext]
                    : (object) [],
                'errors' => (object) [],
            ], $status);
        });
    })->create();
