<?php



declare(strict_types=1);



namespace App\Application\Order\Services;



use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Domain\Product\Services\ProductPriceResolver;

use App\Domain\Product\ValueObjects\SaleMode;



final class OrderItemPricing

{

    public function __construct(

        private readonly ProductRepositoryInterface $products,

        private readonly ProductPriceResolver $priceResolver,

    ) {

    }



    /**

     * @return array{

     *   product_name: string,

     *   unit_price: string,

     *   line_total: string,

     *   girl_amount: string|null,

     *   house_amount: string|null,

     *   currency: string,

     *   girl_amount_per_combo: string|null,

     *   house_amount_per_combo: string|null,

     * }

     */

    public function resolve(

        int $tenantId,

        int $branchId,

        int $productId,

        string $saleMode,

        int $quantity,

    ): array {

        $mode = SaleMode::fromString($saleMode);



        $productPrice = $this->priceResolver->resolve(

            tenantId: $tenantId,

            productId: $productId,

            saleMode: $mode->value,

            branchId: $branchId,

        );



        $product = $this->products->findById($productId, $tenantId);

        $unitPrice = (float) $productPrice->price;

        $lineTotal = round($unitPrice * $quantity, 2);



        $girlPerCombo = $productPrice->girlAmount !== null

            ? (float) $productPrice->girlAmount

            : null;

        $housePerCombo = $productPrice->houseAmount !== null

            ? (float) $productPrice->houseAmount

            : null;



        return [

            'product_name' => $product?->name ?? 'Producto',

            'unit_price' => number_format($unitPrice, 2, '.', ''),

            'line_total' => number_format($lineTotal, 2, '.', ''),

            'girl_amount' => $girlPerCombo !== null

                ? number_format(round($girlPerCombo * $quantity, 2), 2, '.', '')

                : null,

            'house_amount' => $housePerCombo !== null

                ? number_format(round($housePerCombo * $quantity, 2), 2, '.', '')

                : null,

            'currency' => $productPrice->currency,

            'girl_amount_per_combo' => $girlPerCombo !== null

                ? number_format($girlPerCombo, 2, '.', '')

                : null,

            'house_amount_per_combo' => $housePerCombo !== null

                ? number_format($housePerCombo, 2, '.', '')

                : null,

        ];

    }

}


