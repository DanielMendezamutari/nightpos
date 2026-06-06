<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\UseCases;



use App\Application\Cash\Services\ServiceIncomeCashRecorder;

use App\Application\GirlIncome\DTOs\CreateBraceletInput;

use App\Application\GirlIncome\Services\GirlStaffValidator;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;

use App\Domain\GirlIncome\Repositories\BraceletRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;



final class CreateBraceletUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly BraceletRepositoryInterface $bracelets,

        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,

        private readonly GirlStaffValidator $girlStaffValidator,

        private readonly ServiceIncomeCashRecorder $serviceCash,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof CreateBraceletInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $userId = $this->staffContext->userId();



        if ($tenant === null || $branch === null || $userId === null) {

            throw GirlIncomeDomainException::branchRequired();

        }



        if ($input->quantity < 1) {

            throw GirlIncomeDomainException::invalidQuantity();

        }



        $unitPrice = (float) $input->unitPrice;

        if ($unitPrice <= 0) {

            throw GirlIncomeDomainException::invalidAmount();

        }



        $this->girlStaffValidator->assertGirl($tenant->id, $input->girlUserId);

        $this->girlStaffValidator->assertWaiterOptional($tenant->id, $input->waiterUserId);



        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        $total = bcmul((string) $unitPrice, (string) $input->quantity, 2);

        $paymentMethod = $this->serviceCash->normalizePaymentMethod($tenant->id, $branch->id, $input->paymentMethod);

        $cashSession = $this->serviceCash->requireOpenSession($tenant->id, $branch->id, $userId);

        $girlName = (string) (UserModel::query()->whereKey($input->girlUserId)->value('name') ?? 'Chica');



        $entry = DB::transaction(function () use (

            $tenant,

            $branch,

            $shift,

            $input,

            $userId,

            $unitPrice,

            $total,

            $paymentMethod,

            $cashSession,

            $girlName,

        ) {

            $created = $this->bracelets->create(

                tenantId: $tenant->id,

                branchId: $branch->id,

                officialShiftId: $shift->id,

                girlUserId: $input->girlUserId,

                waiterUserId: $input->waiterUserId,

                quantity: $input->quantity,

                unitPrice: number_format($unitPrice, 2, '.', ''),

                totalAmount: $total,

                registeredByUserId: $userId,

                registeredAt: Carbon::now()->format('Y-m-d H:i:s'),

                notes: $input->notes,

                cashSessionId: $cashSession->id,

                paymentMethod: $paymentMethod,

            );



            $movement = $this->serviceCash->recordIncome(

                tenantId: $tenant->id,

                branchId: $branch->id,

                session: $cashSession,

                amount: $total,

                paymentMethod: $paymentMethod,

                description: "Manilla - {$girlName}",

                sourceType: 'BRACELET',

                sourceId: (int) $created['id'],

                createdByUserId: $userId,

            );



            $this->bracelets->attachCashMovement((int) $created['id'], $tenant->id, $movement->id);



            $created['cash_session_id'] = $cashSession->id;

            $created['cash_movement_id'] = $movement->id;

            $created['payment_method'] = $paymentMethod;



            return $created;

        });



        return OperationResult::ok('Manillas registradas correctamente.', [

            'bracelet' => $entry,

            'shift' => [

                'id' => $shift->id,

                'name' => $shift->name,

                'business_date' => $shift->businessDate,

            ],

        ]);

    }

}

