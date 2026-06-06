<?php



declare(strict_types=1);



namespace App\Application\Order\DTOs;



final readonly class ListOrdersInput

{

    public function __construct(

        public ?string $status = null,

        public bool $currentShiftOnly = false,

        public ?string $scope = null,

        public ?int $limit = null,

    ) {

    }

}

