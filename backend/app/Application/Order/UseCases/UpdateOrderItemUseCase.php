<?php



declare(strict_types=1);



namespace App\Application\Order\UseCases;



use App\Application\GirlIncome\Services\GirlStaffValidator;

use App\Application\Order\DTOs\UpdateOrderItemInput;

use App\Application\Order\Services\OrderAccessGuard;

use App\Application\Order\Services\OrderItemPricing;

use App\Application\Order\Support\OrderMapper;

use App\Domain\Order\Exceptions\OrderDomainException;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

use App\Domain\Order\ValueObjects\OrderStatus;

use App\Domain\Product\Entities\Product;

use App\Domain\Product\Exceptions\ProductDomainException;

use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Domain\Product\ValueObjects\SaleMode;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Application\Support\AuditLogRecorder;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class UpdateOrderItemUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly OrderRepositoryInterface $orders,

        private readonly ProductRepositoryInterface $products,

        private readonly OrderAccessGuard $accessGuard,

        private readonly OrderItemPricing $itemPricing,

        private readonly GirlStaffValidator $girlStaffValidator,

        private readonly AuditLogRecorder $audit,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof UpdateOrderItemInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();



        if ($tenant === null || $branch === null) {

            throw OrderDomainException::branchRequired();

        }



        $order = $this->accessGuard->loadOrder($input->orderId);

        $status = $this->accessGuard->assertNotTerminal($order);

        $this->accessGuard->assertAllowsLineChanges($status);



        $item = collect($order->items)->firstWhere('id', $input->itemId);



        if ($item === null) {

            throw OrderDomainException::itemNotFound();

        }



        if ($item->isCancelled()) {

            throw OrderDomainException::itemAlreadyCancelled();

        }



        $isOpen = $status->value === OrderStatus::OPEN;

        $isSentToBar = $status->value === OrderStatus::SENT_TO_BAR;



        if (! $isOpen && ! $isSentToBar) {

            throw OrderDomainException::notModifiable();

        }



        $productChanged = $input->productId !== null && $input->productId !== $item->productId;



        if ($productChanged) {

            if ($isSentToBar && $item->isSent() && trim((string) $input->reason) === '') {

                throw OrderDomainException::changeReasonRequired();

            }

        } elseif ($isSentToBar && ($input->quantity !== null || $input->saleMode !== null)) {

            throw OrderDomainException::onlyGirlChangeAllowed();

        }



        $productId = $productChanged ? $input->productId : $item->productId;

        $product = $this->resolveActiveProduct($tenant->id, $productId);



        $saleMode = $input->saleMode !== null

            ? SaleMode::fromString($input->saleMode)->value

            : $item->saleMode;



        $quantity = $input->quantity ?? $item->quantity;



        if ($quantity < 1) {

            throw OrderDomainException::invalidQuantity();

        }



        $girlUserId = $item->girlUserId;



        if ($input->clearGirl) {

            $girlUserId = null;

        } elseif ($input->girlUserId !== null) {

            $girlUserId = $input->girlUserId;

        } elseif (! SaleMode::fromString($saleMode)->isConAcompanante()) {

            $girlUserId = null;

        }



        if (SaleMode::fromString($saleMode)->isConAcompanante() && $girlUserId !== null) {

            $this->girlStaffValidator->assertGirl($tenant->id, $girlUserId);

        }



        $pricing = $this->itemPricing->resolve(

            tenantId: $tenant->id,

            branchId: $branch->id,

            productId: $productId,

            saleMode: $saleMode,

            quantity: $quantity,

        );



        $this->orders->updateItem(

            tenantId: $tenant->id,

            orderId: $order->id,

            itemId: $item->id,

            productId: $productId,

            productName: $pricing['product_name'],

            saleMode: $saleMode,

            quantity: $quantity,

            unitPrice: $pricing['unit_price'],

            lineTotal: $pricing['line_total'],

            girlAmount: $pricing['girl_amount'],

            houseAmount: $pricing['house_amount'],

            girlUserId: $girlUserId,

        );



        $updated = $this->orders->recalculateTotals($order->id);



        if ($productChanged) {

            $this->audit->record('order.item_product_changed', 'order', $order->id, [

                'item_id' => $item->id,

                'previous_product_id' => $item->productId,

                'previous_product_name' => $item->productName,

                'new_product_id' => $productId,

                'new_product_name' => $pricing['product_name'],

                'reason' => $input->reason,

                'changed_by_user_id' => $this->staffContext->userId(),

            ]);

        } else {

            $this->audit->record('order.item_updated', 'order', $order->id, [

                'item_id' => $item->id,

                'quantity' => $quantity,

                'sale_mode' => $saleMode,

                'girl_user_id' => $girlUserId,

            ]);

        }



        $message = $productChanged

            ? sprintf('Producto cambiado de %s a %s.', $item->productName, $pricing['product_name'])

            : 'Ítem actualizado.';



        return OperationResult::ok($message, [

            'order' => OrderMapper::order($updated),

        ]);

    }



    private function resolveActiveProduct(int $tenantId, int $productId): Product

    {

        $product = $this->products->findById($productId, $tenantId);



        if ($product === null) {

            throw ProductDomainException::notFound();

        }



        if ($product->status !== 'active') {

            throw ProductDomainException::inactiveProduct();

        }



        return $product;

    }

}

