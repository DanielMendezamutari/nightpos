<?php



declare(strict_types=1);



namespace App\Application\Product\DTOs;



final readonly class GetProductsListInput extends ProductDto

{

    public function __construct(

        public bool $includeActivePrices = false,

    ) {

    }

}

