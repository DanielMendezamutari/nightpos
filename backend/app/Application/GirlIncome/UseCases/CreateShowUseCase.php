<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\UseCases;



use App\Application\Cash\Services\ServiceIncomeCashRecorder;

use App\Application\GirlIncome\DTOs\CreateShowInput;

use App\Application\GirlIncome\Services\GirlStaffValidator;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;

use App\Domain\GirlIncome\Repositories\ShowRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;



final class CreateShowUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly ShowRepositoryInterface $shows,

        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,

        private readonly GirlStaffValidator $girlStaffValidator,

        private readonly ServiceIncomeCashRecorder $serviceCash,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof CreateShowInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $userId = $this->staffContext->userId();



        if ($tenant === null || $branch === null || $userId === null) {

            throw GirlIncomeDomainException::branchRequired();

        }



        $unitPrice = (float) $input->unitPrice;

        if ($unitPrice <= 0) {

            throw GirlIncomeDomainException::invalidAmount();

        }



        if (trim($input->showType) === '') {

            return OperationResult::fail('Debe indicar el tipo de show.');

        }



        $this->girlStaffValidator->assertGirl($tenant->id, $input->girlUserId);



        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        $formatted = number_format($unitPrice, 2, '.', '');

        $paymentMethod = $this->serviceCash->normalizePaymentMethod($tenant->id, $branch->id, $input->paymentMethod);

        $cashSession = $this->serviceCash->requireOpenSession($tenant->id, $branch->id, $userId);

        $girlName = (string) (UserModel::query()->whereKey($input->girlUserId)->value('name') ?? 'Chica');

        $showType = strtoupper(trim($input->showType));



        $registeredAt = $input->registeredAt !== null

            ? Carbon::parse($input->registeredAt)->format('Y-m-d H:i:s')

            : Carbon::now()->format('Y-m-d H:i:s');



        $entry = DB::transaction(function () use (

            $tenant,

            $branch,

            $shift,

            $input,

            $userId,

            $formatted,

            $paymentMethod,

            $cashSession,

            $girlName,

            $showType,

            $registeredAt,

        ) {

            $created = $this->shows->create(

                tenantId: $tenant->id,

                branchId: $branch->id,

                officialShiftId: $shift->id,

                girlUserId: $input->girlUserId,

                showType: $showType,

                unitPrice: $formatted,

                totalAmount: $formatted,

                registeredByUserId: $userId,

                registeredAt: $registeredAt,

                notes: $input->notes,

                cashSessionId: $cashSession->id,

                paymentMethod: $paymentMethod,

            );



            $movement = $this->serviceCash->recordIncome(

                tenantId: $tenant->id,

                branchId: $branch->id,

                session: $cashSession,

                amount: $formatted,

                paymentMethod: $paymentMethod,

                description: "Show - {$showType} - {$girlName}",

                sourceType: 'SHOW',

                sourceId: (int) $created['id'],

                createdByUserId: $userId,

            );



            $this->shows->attachCashMovement((int) $created['id'], $tenant->id, $movement->id);



            $created['cash_session_id'] = $cashSession->id;

            $created['cash_movement_id'] = $movement->id;

            $created['payment_method'] = $paymentMethod;



            return $created;

        });



        return OperationResult::ok('Show registrado correctamente.', [

            'show' => $entry,

            'shift' => [

                'id' => $shift->id,

                'name' => $shift->name,

                'business_date' => $shift->businessDate,

            ],

        ]);

    }

}

