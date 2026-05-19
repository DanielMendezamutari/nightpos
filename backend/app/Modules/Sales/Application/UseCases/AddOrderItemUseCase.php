<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\UseCases;

use App\Modules\Sales\Application\DTO\AddOrderItemInput;
use App\Modules\Sales\Domain\Ports\OrderItemRepository;
use App\Modules\Sales\Domain\Ports\ProductPricingRepository;
use App\Modules\Sales\Domain\Services\PricingPolicy;

final readonly class AddOrderItemUseCase
{
    public function __construct(
        private PricingPolicy $pricingPolicy,
        private OrderItemRepository $orderItemRepository,
        private ProductPricingRepository $productPricingRepository,
    ) {
    }

    public function execute(AddOrderItemInput $input): void
    {
        $pricing = $this->productPricingRepository->getPricingByProductId($input->productId);

        $unitPrice = $this->pricingPolicy->resolveDrinkPrice(
            consumptionType: $input->consumptionType,
            priceSolo: $pricing['price_solo'],
            priceWithCompanion: $pricing['price_with_companion'],
        );

        $this->orderItemRepository->store([
            'order_id' => $input->orderId,
            'product_id' => $input->productId,
            'waiter_user_id' => $input->waiterId,
            'companion_id' => $input->companionId,
            'consumption_type' => $input->consumptionType->value,
            'quantity' => $input->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $input->quantity,
            'registered_at' => now(),
        ]);
    }
}
