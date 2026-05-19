<?php

namespace App\Providers;

use App\Events\PaymentRegistered;
use App\Listeners\RecordJournalEntryForPayment;
use App\Modules\Cashier\Domain\Ports\PaymentRepository;
use App\Modules\Cashier\Domain\Ports\ShiftRepository;
use App\Modules\Cashier\Infrastructure\Persistence\DbPaymentRepository;
use App\Modules\Cashier\Infrastructure\Persistence\DbShiftRepository;
use App\Modules\Sales\Domain\Ports\OrderItemRepository;
use App\Modules\Sales\Domain\Ports\ProductPricingRepository;
use App\Modules\Sales\Infrastructure\Persistence\DbProductPricingRepository;
use App\Modules\Sales\Infrastructure\Persistence\DbOrderItemRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ShiftRepository::class, DbShiftRepository::class);
        $this->app->bind(PaymentRepository::class, DbPaymentRepository::class);
        $this->app->bind(OrderItemRepository::class, DbOrderItemRepository::class);
        $this->app->bind(ProductPricingRepository::class, DbProductPricingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PaymentRegistered::class, RecordJournalEntryForPayment::class);
    }
}
