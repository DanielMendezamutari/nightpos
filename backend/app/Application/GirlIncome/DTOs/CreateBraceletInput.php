<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\DTOs;



final readonly class CreateBraceletInput

{

    public function __construct(

        public int $girlUserId,

        public int $quantity,

        public string $unitPrice,

        public string $paymentMethod,

        public ?int $waiterUserId = null,

        public ?string $notes = null,

    ) {

    }

}

