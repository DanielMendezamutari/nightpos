<?php



declare(strict_types=1);



namespace App\Shared\Domain\Enums;



enum StaffFineStatus: string

{

    case Pending = 'PENDING';

    case Applied = 'APPLIED';

    case Cancelled = 'CANCELLED';

}


