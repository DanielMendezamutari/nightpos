<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\DTOs;



final readonly class CreateRoomServiceInput

{

    public function __construct(

        public int $girlUserId,

        public string $totalAmount,

        public string $girlPercent,

        public string $paymentMethod,

        public ?int $roomId = null,

        public ?string $roomLabel = null,

        public ?string $roomNumber = null,

        public ?int $durationMinutes = null,

        public ?string $startedAt = null,

        public ?string $notes = null,

        public ?string $cleaningAmount = null,

    ) {

    }

}

