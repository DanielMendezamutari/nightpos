<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Salon\Repositories\SalonRepositoryInterface;
use App\Domain\Mesa\Repositories\MesaRepositoryInterface;
use App\Domain\Menu\Repositories\MenuRepositoryInterface;
use App\Domain\Pedido\Repositories\PedidoRepositoryInterface;
use App\Infrastructure\Auth\JwtTokenGenerator;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalonRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentMesaRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentMenuRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPedidoRepository;
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
    }

    public function boot(): void
    {
    }
}