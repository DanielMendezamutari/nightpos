<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Salon\Repositories\SalonRepositoryInterface;
use App\Domain\Mesa\Repositories\MesaRepositoryInterface;
use App\Domain\Menu\Repositories\MenuRepositoryInterface;
use App\Domain\Pedido\Repositories\PedidoRepositoryInterface;
use App\Domain\Caja\Repositories\CajaRepositoryInterface;
use App\Domain\Factura\Repositories\FacturaRepositoryInterface;
use App\Infrastructure\Auth\JwtTokenGenerator;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalonRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentMesaRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentMenuRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPedidoRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCajaRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentFacturaRepository;
use App\Infrastructure\Facturacion\SiatBoliviaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(TokenGeneratorInterface::class, JwtTokenGenerator::class);
        $this->app->bind(SalonRepositoryInterface::class, EloquentSalonRepository::class);
        $this->app->bind(MesaRepositoryInterface::class, EloquentMesaRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, EloquentMenuRepository::class);
        $this->app->bind(PedidoRepositoryInterface::class, EloquentPedidoRepository::class);
        $this->app->bind(CajaRepositoryInterface::class, EloquentCajaRepository::class);
        $this->app->bind(FacturaRepositoryInterface::class, EloquentFacturaRepository::class);
        $this->app->singleton(SiatBoliviaService::class, fn() => new SiatBoliviaService());
    }

    public function boot(): void
    {
    }
}