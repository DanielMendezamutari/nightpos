<?php
declare(strict_types=1);
namespace App\Providers;
use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Auth\JwtTokenGenerator;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider { public function register(): void { $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class); $this->app->bind(TokenGeneratorInterface::class, JwtTokenGenerator::class); } public function boot(): void {} }