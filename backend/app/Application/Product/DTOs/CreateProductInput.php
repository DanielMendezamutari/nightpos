<?php



declare(strict_types=1);



namespace App\Application\Product\DTOs;



final readonly class CreateProductInput extends ProductDto

{

    public function __construct(

        public string $name,

        public ?int $branchId = null,

        public ?int $categoryId = null,

        public ?string $sku = null,

        public ?string $barcode = null,

        public ?string $description = null,

        public string $productType = 'beverage',

        public string $unit = 'unit',

        public bool $trackInventory = false,

        public string $status = 'active',

        public string $settlementBehavior = 'GIRL_LINE',

        public int $braceletUnitsPerLine = 1,

    ) {

    }

}


