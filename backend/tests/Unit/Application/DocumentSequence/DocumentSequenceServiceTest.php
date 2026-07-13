<?php

declare(strict_types=1);

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, DatabaseTransactions::class);

/**
 * @return array{branch: BranchModel, shift: OfficialShiftModel, girl: UserModel}
 */
function makeDocumentSequenceContext(): array
{
    $suffix = uniqid();
    $tenant = TenantModel::query()->create([
        'name' => 'Test Tenant '.$suffix,
        'slug' => 'test-tenant-'.$suffix,
        'status' => 'active',
    ]);

    $branch = BranchModel::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Centro Test '.$suffix,
        'code' => 'CENTRO_'.$suffix,
        'status' => 'active',
    ]);

    $girl = UserModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Chica Test '.$suffix,
        'username' => 'chica_'.$suffix,
        'status' => 'active',
    ]);

    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Turno Test '.$suffix,
        'shift_type' => 'NIGHT',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->subHours(2),
        'ends_at' => now()->addHours(6),
        'status' => 'OPEN',
        'opened_by_user_id' => $girl->id,
        'opened_at' => now()->subHours(2),
    ]);

    return [
        'branch' => $branch,
        'shift' => $shift,
        'girl' => $girl,
    ];
}

it('syncs lagged last_value to max existing ticket before incrementing', function () {
    $ctx = makeDocumentSequenceContext();
    $branch = $ctx['branch'];
    $shift = $ctx['shift'];
    $girlId = (int) $ctx['girl']->id;
    $year = now()->format('Y');

    StaffSettlementModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => $girlId,
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 40,
        'gross_amount' => 40,
        'adjustments_total' => 0,
        'net_amount' => 40,
        'status' => 'PAID',
        'paid_at' => now(),
        'ticket_number' => 'CENTRO-'.$year.'-000002',
    ]);

    DocumentSequenceModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'document_type' => DocumentSequenceType::SettlementPayment->value,
        'period_key' => $year,
        'last_value' => 1,
    ]);

    $service = app(DocumentSequenceService::class);

    expect($service->maxSettlementTicketSequence((int) $branch->tenant_id, (int) $branch->id, $year))
        ->toBe(2);

    $next = DB::transaction(fn () => $service->reserveNext(
        (int) $branch->tenant_id,
        (int) $branch->id,
        DocumentSequenceType::SettlementPayment,
        $year,
    ));

    expect($next)->toBe(3)
        ->and($service->currentValue(
            (int) $branch->tenant_id,
            (int) $branch->id,
            DocumentSequenceType::SettlementPayment,
            $year,
        ))->toBe(3);
});

it('starts new sequence rows at zero and returns one on first reservation', function () {
    $ctx = makeDocumentSequenceContext();
    $branch = $ctx['branch'];
    $year = now()->format('Y');
    $service = app(DocumentSequenceService::class);

    $next = DB::transaction(fn () => $service->reserveNext(
        (int) $branch->tenant_id,
        (int) $branch->id,
        DocumentSequenceType::SettlementPayment,
        $year,
    ));

    expect($next)->toBe(1)
        ->and(DocumentSequenceModel::query()->where([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'document_type' => DocumentSequenceType::SettlementPayment->value,
            'period_key' => $year,
        ])->value('last_value'))->toBe(1);
});
