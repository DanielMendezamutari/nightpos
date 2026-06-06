<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\UseCases;



use App\Application\Cash\Services\ServiceIncomeCashRecorder;

use App\Application\GirlIncome\DTOs\CreateRoomServiceInput;

use App\Application\GirlIncome\Services\GirlStaffValidator;
use App\Application\SSE\Services\OperationalEventEmitter;

use App\Application\GirlIncome\Support\RoomServiceAmountCalculator;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;

use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;

use App\Domain\Room\Exceptions\RoomNotFoundException;

use App\Domain\Room\Repositories\RoomRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;



final class CreateRoomServiceUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly RoomServiceRepositoryInterface $roomServices,

        private readonly RoomRepositoryInterface $rooms,

        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,

        private readonly GirlStaffValidator $girlStaffValidator,

        private readonly ServiceIncomeCashRecorder $serviceCash,

        private readonly OperationalEventEmitter $eventEmitter,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof CreateRoomServiceInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $userId = $this->staffContext->userId();



        if ($tenant === null || $branch === null || $userId === null) {

            throw GirlIncomeDomainException::branchRequired();

        }



        $totalAmount = (float) $input->totalAmount;

        $girlPercent = (float) $input->girlPercent;

        $cleaningAmount = $input->cleaningAmount !== null ? (float) $input->cleaningAmount : 0.0;



        if ($totalAmount <= 0) {

            throw GirlIncomeDomainException::invalidAmount();

        }



        if ($girlPercent < 0 || $girlPercent > 100) {

            throw GirlIncomeDomainException::invalidGirlPercent();

        }



        $grossGirlAmount = round($totalAmount * $girlPercent / 100, 2);

        if ($cleaningAmount > $grossGirlAmount) {

            throw GirlIncomeDomainException::cleaningExceedsGirlAmount();

        }



        $split = RoomServiceAmountCalculator::split($totalAmount, $girlPercent, $cleaningAmount);

        $duration = $input->durationMinutes ?? 60;



        if ($duration < 1 || $duration > 24 * 60) {

            throw GirlIncomeDomainException::invalidDuration();

        }



        $this->girlStaffValidator->assertGirl($tenant->id, $input->girlUserId);

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);



        $totalFormatted = number_format($totalAmount, 2, '.', '');

        $girlPercentFormatted = number_format($girlPercent, 2, '.', '');

        $paymentMethod = $this->serviceCash->normalizePaymentMethod($tenant->id, $branch->id, $input->paymentMethod);

        $cashSession = $this->serviceCash->requireOpenSession($tenant->id, $branch->id, $userId);

        $girlName = (string) (UserModel::query()->whereKey($input->girlUserId)->value('name') ?? 'Chica');



        $tz = config('app.timezone', 'America/La_Paz');

        $started = $input->startedAt !== null

            ? Carbon::parse($input->startedAt, $tz)

            : Carbon::now($tz);



        $expectedEnds = $started->copy()->addMinutes($duration);

        $registeredAt = Carbon::now();



        $roomId = $input->roomId;

        $roomNumber = $input->roomNumber;

        $roomLabel = $input->roomLabel ?? $input->roomNumber;



        if ($roomId !== null) {

            $room = $this->rooms->findById($roomId, $tenant->id, $branch->id);



            if ($room === null) {

                throw new RoomNotFoundException();

            }



            $roomLabel = $room['name'];

            $roomNumber = $room['code'];

        }



        $entry = DB::transaction(function () use (

            $tenant,

            $branch,

            $shift,

            $input,

            $roomId,

            $roomNumber,

            $roomLabel,

            $totalFormatted,

            $girlPercentFormatted,

            $split,

            $userId,

            $registeredAt,

            $started,

            $duration,

            $expectedEnds,

            $paymentMethod,

            $cashSession,

            $girlName,

        ) {

            if ($roomId !== null && ! $this->rooms->occupyIfAvailable($roomId, $tenant->id, $branch->id)) {

                throw GirlIncomeDomainException::roomNotAvailable();

            }



            $created = $this->roomServices->create(

                tenantId: $tenant->id,

                branchId: $branch->id,

                officialShiftId: $shift->id,

                girlUserId: $input->girlUserId,

                roomId: $roomId,

                roomNumber: $roomNumber,

                roomLabel: $roomLabel,

                unitPrice: $totalFormatted,

                totalAmount: $totalFormatted,

                girlPercent: $girlPercentFormatted,

                grossGirlAmount: $split['gross_girl_amount'],

                girlAmount: $split['girl_amount'],

                houseAmount: $split['house_amount'],

                cleaningAmount: $split['cleaning_amount'],

                registeredByUserId: $userId,

                registeredAt: $registeredAt->format('Y-m-d H:i:s'),

                startedAt: $started->format('Y-m-d H:i:s'),

                durationMinutes: $duration,

                expectedEndsAt: $expectedEnds->format('Y-m-d H:i:s'),

                notes: $input->notes,

                cashSessionId: $cashSession->id,

                paymentMethod: $paymentMethod,

            );



            $label = $roomLabel ?? $roomNumber ?? 'pieza';

            $movement = $this->serviceCash->recordIncome(

                tenantId: $tenant->id,

                branchId: $branch->id,

                session: $cashSession,

                amount: $totalFormatted,

                paymentMethod: $paymentMethod,

                description: "Pieza - {$label} - {$girlName}",

                sourceType: 'ROOM_SERVICE',

                sourceId: (int) $created['id'],

                createdByUserId: $userId,

            );



            $this->roomServices->attachCashMovement((int) $created['id'], $tenant->id, $movement->id);



            $created['cash_session_id'] = $cashSession->id;

            $created['cash_movement_id'] = $movement->id;

            $created['payment_method'] = $paymentMethod;



            return $created;

        });



        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'room_service.created',
            [
                'entity'   => ['type' => 'room_service', 'id' => (int) $entry['id']],
                'summary'  => 'Pieza registrada: ' . ($entry['room_label'] ?? $entry['room_number'] ?? ''),
                'refresh'  => ['room_services', 'rooms'],
            ]
        );

        return OperationResult::ok('Pieza registrada correctamente.', [

            'room_service' => $entry,

            'shift' => [

                'id' => $shift->id,

                'name' => $shift->name,

                'business_date' => $shift->businessDate,

            ],

        ]);

    }

}

