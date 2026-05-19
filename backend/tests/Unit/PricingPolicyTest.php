<?php

use App\Modules\Sales\Domain\Enums\ConsumptionType;
use App\Modules\Sales\Domain\Services\PricingPolicy;

it('returns solo product price for solo consumption', function (): void {
    $policy = new PricingPolicy();

    expect($policy->resolveDrinkPrice(
        ConsumptionType::SOLO,
        priceSolo: 55,
        priceWithCompanion: 95
    ))->toBe(55);
});

it('returns product price with companion for with_companion consumption', function (): void {
    $policy = new PricingPolicy();

    expect($policy->resolveDrinkPrice(
        ConsumptionType::WITH_COMPANION,
        priceSolo: 55,
        priceWithCompanion: 95
    ))->toBe(95);
});
