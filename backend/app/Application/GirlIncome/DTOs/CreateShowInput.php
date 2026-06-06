<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\DTOs;



final readonly class CreateShowInput

{

    public function __construct(

        public int $girlUserId,

        public string $showType,

        public string $unitPrice,

        public string $paymentMethod,

        public ?string $registeredAt = null,

        public ?string $notes = null,

    ) {

    }

}

