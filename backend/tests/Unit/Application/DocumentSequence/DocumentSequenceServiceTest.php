<?php

declare(strict_types=1);

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('syncs lagged last_value to max existing ticket before incrementing', function () {
    $branch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')
        ->firstOrFail();
    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();
    $year = now()->format('Y');
    $girlId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

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
    $branch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')
        ->firstOrFail();
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
